import { basicSetup, EditorView } from 'codemirror';
import { sql, SQLite } from '@codemirror/lang-sql';

const workspace = document.querySelector('#sql-workspace');

if (workspace) {
    const day = workspace.dataset.day;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const feedback = document.querySelector('#feedback');
    const results = document.querySelector('#results');
    let saveTimer;

    const editor = new EditorView({
        doc: workspace.dataset.sql || 'SELECT ',
        extensions: [
            basicSetup,
            sql({ dialect: SQLite }),
            EditorView.theme({
                '&': { height: '330px', backgroundColor: '#020617', color: '#e2e8f0' },
                '.cm-gutters': { backgroundColor: '#0f172a', color: '#64748b', border: 'none' },
                '.cm-scroller': { overflow: 'auto' },
            }),
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    clearTimeout(saveTimer);
                    saveTimer = setTimeout(saveDraft, 800);
                }
            }),
        ],
        parent: document.querySelector('#editor'),
    });

    async function saveDraft() {
        await fetch(`/challenges/${day}/draft`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: JSON.stringify({ sql: editor.state.doc.toString() }),
        });
    }

    function renderResults(data) {
        results.replaceChildren();
        if (!data.rows) return;

        const table = document.createElement('table');
        const header = table.createTHead().insertRow();
        data.columns.forEach((column) => {
            const cell = document.createElement('th');
            cell.textContent = column;
            header.append(cell);
        });

        const body = table.createTBody();
        data.rows.forEach((row) => {
            const tableRow = body.insertRow();
            data.columns.forEach((column) => {
                const cell = tableRow.insertCell();
                cell.textContent = row[column] === null ? 'NULL' : String(row[column]);
            });
        });
        results.append(table);

        if (data.truncated) {
            const note = document.createElement('p');
            note.textContent = 'Showing the first 250 rows.';
            note.className = 'mt-2 text-sm text-slate-400';
            results.append(note);
        }

        if (data.solution) {
            const heading = document.createElement('h3');
            heading.className = 'mt-6 font-semibold text-blue-300';
            heading.textContent = 'Reference solution';
            const answer = document.createElement('pre');
            answer.className = 'solution';
            answer.textContent = data.solution;
            results.append(heading, answer);
        }
    }

    async function runQuery() {
        feedback.textContent = 'Checking…';
        document.querySelector('#run').disabled = true;

        try {
            const response = await fetch(`/challenges/${day}/attempt`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify({ sql: editor.state.doc.toString() }),
            });
            const data = await response.json();
            feedback.textContent = data.error || data.feedback || data.message || 'Could not check the query.';
            feedback.className = `mt-4 text-sm ${data.passed ? 'text-emerald-400' : 'text-amber-300'}`;
            renderResults(data);
        } catch {
            feedback.textContent = 'Could not reach the local app. Try again.';
        } finally {
            document.querySelector('#run').disabled = false;
        }
    }

    document.querySelector('#run').addEventListener('click', runQuery);
    document.querySelector('#hint').addEventListener('click', () => document.querySelector('#hint-text').classList.toggle('hidden'));
    editor.dom.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
            event.preventDefault();
            runQuery();
        }
    });
}
