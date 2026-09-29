DATABASE
---
- ==`CREATE DATABASE NameOfTheDatabase`==    to create a database
- ==`DROP DATABASE NameOfTheDatabase`==     to delete a database 
- ==`ALTER DATABASE NameOfTheDatabase READ ONLY = 1`==   this makes the database read-only

------

TABLES
---
- ==`CREATE TABLE users (
		`user_ID INT PRIMARY KEY,
	    `user_NAME VARCHAR(30) NOT NULL UNIQUE,
	    `user_PHONE VARCHAR(11),
	    `user_AGE INT,
	    `user_STATUS VARCHAR(20) DEFAULT 'active',`
		`user_BALANCE DECIMAL(10, 2),`
	    `user_REGISTERDATE DATE
	`);`==

- ==`SELECT * FROM TableName`==  to get all rows and cols from that table 
- ==`RENAME TABLE TableName TO NewTableName`==  to rename a table
- ==`ALTER TABLE NameOfTable ADD NewColName TypeOfNewCol`==  to add a new col to the table 
- ==`FOREIGN KEY (user_id) REFRENCES users(id)`== 

-----------
FILTERING
---

- ==`ORDER BY age ASC`==  this is to order the table ascending using age can also use `DESC`
- ==`LIMIT 5`==  to get only top 5 rows 
- ==`WHERE name LIKE 'H%'`==  this means to get all names that start with H and % means any num of chars after it, like it doesn't matter and it could also mean 0 chars 
- ==`WHERE name NOT LIKE 'A%'`==   to get all names thats doesn't start with A 
- ==`WHERE age IN (20, 25, 30)`== is the same as ==`WHERE age = 20 OR age = 25 OR age = 30`== you can also use NOT IN for the opposite 
- ==`WHERE age BETWEEN 18 AND 25`== this is inclusive (also returns the 18 and 25)
- ==`WHERE email IS NULL  ///  WHERE email IS NOT NULL`==
- ==`SELECT user_NAME AS userName`==  to return the col with diff name without changing it 
- ==`SELECT COUNT(*) AS Total_users FROM users`==  to get how many rows are there 
- ==`SELECT AVG(age) AS average_age FROM users`==   this is to get the average age 
- ==`SELECT MAX(age) AS average_age FROM users`==   this is to get the max age 
- ==`SELECT MIN(age) AS average_age FROM users`==   this is to get the min age 
- ==`SELECT SUM(age) AS average_age FROM users`==   this is to get the sum of ages 
- ==`GROUP BY age`==  to make groups of users with the same age 
- ==`HAVING COUNT(*) > 5`==  only groups with more than 5 users are returned 

------
INSERT
---
- ==`INSERT INTO users (name, email, phone) VALUES ('abdo', 'abdo@gmail', 010)`==
- VALUES could have more than one without parent parentheses only ' , '
------------
UPDATE
---
- ==`UPDATE users SET age = 90 WHERE name = 'abdo'`==  you can stack set and separate them using ' , ' like this 
- ==`UPDATE users SET age = 80, email = 'newemail' WHERE name = 'abdo'`== 
-------
DELETE
---
- ==`DELETE FROM users WHERE name = 'Abdo'`==
----
JOIN
---
==`SELECT posts.title, users.name FROM posts JOIN users ON posts.user_id = users.id`==
JOIN users = bring the users table 
ON ..... and tell whats the connection between the two tables 

------
INNER JOIN
---
only shows results where there's a value on both sides 
==`SELECT users.names, posts.titles FROM users INNER JOIN posts ON users.id = posts.user_id`==
only users which have posts will appear 

-------
LEFT JOIN
---
==`SELECT users.name, posts.titles FROM users LEFT JOIN posts ON users.id = posts.user_id`==
this will show all the users and if they don't have posts it will appear null with no problems

note, if there's more than one appearance of something it will have more than one row in the final joined table.

--------------
JOIN NOTES
---
- you can add a condition in the ON with AND.

---------
SUBQUERIES
---
==`SELECT * FROM users WHERE age > (SELECT AVG(age) FROM users )`==
this returns the users with age above the avg, the subquery always runs first 
it can also return multiple values using IN 

--
==`SELECT category, AVG(price) AS avg_price
`FROM products 
`WHERE price > 50 
`GROUP BY category`==
this returns the avg price of each category 

--
you can use EXISTS to check whether the subquery got nay matches or no 
like so 
`SELECT
   `` p.product_name,
   `` p.category
`FROM products AS p
`WHERE EXISTS (
    `SELECT 1
    `FROM sales AS s
    `WHERE s.product_id = p.id
     ``AND s.quantity >= 5
`);

SELECT 1 just means to not return anything and just tell if there was any matches or no
there's also NOT EXISTS which does the opposite 

--



-------------------
MANY TO MANY
---
![[Pasted image 20260815180751.png]]

---------------------------
NOTES 
---
- `AS total_sum` can be treated as a value. 
- `COUNT(age)` this counts only the rows with age not null.
-  you can also use COALESCE to show a known value like so
   `COALESCE(posts.titles, 'no titles') AS titles` this is used with the value in the select.
------------
CASE
 ---
 `SELECT
     `product,
     `quantity * unit_price AS revenue,
     `CASE
         `WHEN quantity * unit_price >= 5000 THEN 'High'
         `ELSE 'Normal'
     `END AS performance
 `FROM sales;

---
DERIVED TABLE
---
`SELECT region_totals.region, region_totals.total_revenue
`FROM (
   `` SELECT
   ``     region,
   ``     SUM(quantity * unit_price) AS total_revenue
   `` FROM sales
   `` GROUP BY region
`) AS region_totals
`WHERE region_totals.total_revenue > 5000;

This way you can make a new temp table to get FROM from it 

------
CTE
---
Its basically the same as derived tables but pre made before anything with WITH like so 
`WITH region_totals AS (
    `SELECT
        `region,
        `SUM(quantity * unit_price) AS total_revenue
    `FROM sales
    `GROUP BY region
`)
`SELECT region, total_revenue
`FROM region_totals
`WHERE total_revenue > 5000
`ORDER BY total_revenue DESC;

--
This table you made can also be joined on or to.
you can also make more than one table if u separate them by a ' , ' like so 
`WITH product_totals AS (
   `` SELECT
   ``     product_id,
   ``     SUM(quantity * unit_price) AS total_revenue
   `` FROM sales
   `` GROUP BY product_id
`),
`average_product_total AS (
   `` SELECT AVG(total_revenue) AS average_revenue
   `` FROM product_totals
`);

---
WINDOW FUNCTION 
---
- ROW NUMBER basic
  ![[Pasted image 20260926072909.png]]
- ROW NUMBER partition 
  `ROW_NUMBER() OVER (
  `PARTITIONED BY salesperson
  `ORDER BY revenue DESC
  `);
  this makes a ranking for each sales person alone inside the same col and table 
  ![[Pasted image 20260926073500.png]]
- Difference between ROW NUMBER, RANK, DENSE RANK
  ![[Pasted image 20260926073928.png]]
- SUM OVER alone 
  ![[Pasted image 20260926074437.png]]
- ROUND 
  On top of OVER you can use ROUND to round up the number to how many decimals like this 
  ![[Pasted image 20260926075536.png]]
- SUM OVER 
  `SUM(quantity * unit_price) OVER (ORDER BY id) AS running_revenue
  this works by sorting the rows by id (only for the calculation not display) and adding them one by one to the window of the function OVER which makes the running_revenue of id 1 = id 1's revenue, id 2 = id 1 + id 2's revenues and so on until the window has all and at the last row there will be the total revenue.
- LAG
  it lets the current row look at the previous row 
  so if you do something like this 
  `SELECT LAG(quantity * unit_price) OVER (ORDER BY id)`
  this would start with null and then get the last row's revenue not the current 
  this allows you to find the difference in revenue and see if revenue is up or down.
- LEAD
  its the same as lag but going forward not previous.

----


