<?php

namespace App\Domain\Practice;

use InvalidArgumentException;

class DatasetCatalog
{
    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return [
            'retail' => $this->dataset('Retail sales', 'Regions, products, and sales.',
                ['regions' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', '']], 'products' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', ''], ['category', 'TEXT', ''], ['unit_price', 'DECIMAL', '']], 'sales' => [['id', 'INTEGER', 'PK'], ['region_id', 'INTEGER', 'FK'], ['product_id', 'INTEGER', 'FK'], ['event_date', 'DATE', ''], ['quantity', 'INTEGER', ''], ['value', 'DECIMAL', '']]],
                ['sales.region_id → regions.id', 'sales.product_id → products.id'],
                ['CREATE TABLE regions (id INTEGER PRIMARY KEY, name TEXT NOT NULL)', 'CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT NOT NULL, category TEXT, unit_price NUMERIC)', 'CREATE TABLE sales (id INTEGER PRIMARY KEY, region_id INTEGER, product_id INTEGER, event_date TEXT, quantity INTEGER, value NUMERIC)'],
                ["INSERT INTO regions VALUES (1,'Cairo'),(2,'Alexandria'),(3,'Giza'),(4,'Mansoura')", "INSERT INTO products VALUES (1,'Laptop','Electronics',28000),(2,'Mouse','Accessories',800),(3,'Monitor','Electronics',7200),(4,'Chair','Furniture',4500),(5,'Webcam','Accessories',2100)", "INSERT INTO sales VALUES (1,1,1,'2026-01-03',2,56000),(2,1,2,'2026-01-07',5,4000),(3,2,3,'2026-02-02',3,21600),(4,3,4,'2026-02-11',2,9000),(5,2,2,'2026-03-01',8,6400),(6,1,3,'2026-03-04',2,14400),(7,3,5,'2026-03-18',6,12600),(8,1,4,'2026-04-02',1,4500)"],
                ['parent' => 'regions', 'parent_name' => 'region', 'item' => 'products', 'item_name' => 'product', 'fact' => 'sales', 'parent_fk' => 'region_id', 'item_fk' => 'product_id']),
            'commerce' => $this->dataset('E-commerce', 'Customers, products, and order lines.',
                ['customers' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', ''], ['city', 'TEXT', '']], 'products' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', ''], ['category', 'TEXT', ''], ['unit_price', 'DECIMAL', '']], 'orders' => [['id', 'INTEGER', 'PK'], ['customer_id', 'INTEGER', 'FK'], ['product_id', 'INTEGER', 'FK'], ['event_date', 'DATE', ''], ['quantity', 'INTEGER', ''], ['value', 'DECIMAL', '']]],
                ['orders.customer_id → customers.id', 'orders.product_id → products.id'],
                ['CREATE TABLE customers (id INTEGER PRIMARY KEY, name TEXT NOT NULL, city TEXT)', 'CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT NOT NULL, category TEXT, unit_price NUMERIC)', 'CREATE TABLE orders (id INTEGER PRIMARY KEY, customer_id INTEGER, product_id INTEGER, event_date TEXT, quantity INTEGER, value NUMERIC)'],
                ["INSERT INTO customers VALUES (1,'Laila','Cairo'),(2,'Omar','Giza'),(3,'Hana','Alexandria'),(4,'Karim','Cairo'),(5,'Salma',NULL)", "INSERT INTO products VALUES (1,'Headphones','Audio',1800),(2,'USB Cable','Accessories',250),(3,'Keyboard','Accessories',1500),(4,'Webcam','Video',2100),(5,'Desk Lamp','Office',600)", "INSERT INTO orders VALUES (1,1,1,'2026-02-01',1,1800),(2,1,2,'2026-02-01',2,500),(3,2,3,'2026-02-03',1,1500),(4,3,4,'2026-02-17',2,4200),(5,4,2,'2026-03-01',3,750),(6,2,1,'2026-03-03',2,3600),(7,5,5,'2026-03-08',2,1200),(8,3,3,'2026-03-15',1,1500)"],
                ['parent' => 'customers', 'parent_name' => 'customer', 'item' => 'products', 'item_name' => 'product', 'fact' => 'orders', 'parent_fk' => 'customer_id', 'item_fk' => 'product_id']),
            'workforce' => $this->dataset('Workforce', 'Departments, employees, and performance reviews.',
                ['departments' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', '']], 'employees' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', ''], ['category', 'TEXT', ''], ['unit_price', 'DECIMAL', '']], 'reviews' => [['id', 'INTEGER', 'PK'], ['department_id', 'INTEGER', 'FK'], ['employee_id', 'INTEGER', 'FK'], ['event_date', 'DATE', ''], ['quantity', 'INTEGER', ''], ['value', 'DECIMAL', '']]],
                ['reviews.department_id → departments.id', 'reviews.employee_id → employees.id'],
                ['CREATE TABLE departments (id INTEGER PRIMARY KEY, name TEXT NOT NULL)', 'CREATE TABLE employees (id INTEGER PRIMARY KEY, name TEXT NOT NULL, category TEXT, unit_price NUMERIC)', 'CREATE TABLE reviews (id INTEGER PRIMARY KEY, department_id INTEGER, employee_id INTEGER, event_date TEXT, quantity INTEGER, value NUMERIC)'],
                ["INSERT INTO departments VALUES (1,'Analytics'),(2,'Engineering'),(3,'Marketing'),(4,'Finance')", "INSERT INTO employees VALUES (1,'Ali','Analyst',15000),(2,'Rana','Analyst',11000),(3,'Hossam','Developer',18000),(4,'Mariam','Marketing',12500),(5,'Nabil','Developer',12000)", "INSERT INTO reviews VALUES (1,1,1,'2025-06-30',1,88),(2,1,1,'2025-12-31',2,92),(3,1,2,'2025-12-31',1,81),(4,2,3,'2025-06-30',1,84),(5,2,3,'2025-12-31',2,87),(6,3,4,'2025-12-31',1,90),(7,2,5,'2025-12-31',1,78)"],
                ['parent' => 'departments', 'parent_name' => 'department', 'item' => 'employees', 'item_name' => 'employee', 'fact' => 'reviews', 'parent_fk' => 'department_id', 'item_fk' => 'employee_id']),
            'subscriptions' => $this->dataset('Subscriptions', 'Plans, customers, and subscription activity.',
                ['plans' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', '']], 'customers' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', ''], ['category', 'TEXT', ''], ['unit_price', 'DECIMAL', '']], 'subscriptions' => [['id', 'INTEGER', 'PK'], ['plan_id', 'INTEGER', 'FK'], ['customer_id', 'INTEGER', 'FK'], ['event_date', 'DATE', ''], ['quantity', 'INTEGER', ''], ['value', 'DECIMAL', '']]],
                ['subscriptions.plan_id → plans.id', 'subscriptions.customer_id → customers.id'],
                ['CREATE TABLE plans (id INTEGER PRIMARY KEY, name TEXT NOT NULL)', 'CREATE TABLE customers (id INTEGER PRIMARY KEY, name TEXT NOT NULL, category TEXT, unit_price NUMERIC)', 'CREATE TABLE subscriptions (id INTEGER PRIMARY KEY, plan_id INTEGER, customer_id INTEGER, event_date TEXT, quantity INTEGER, value NUMERIC)'],
                ["INSERT INTO plans VALUES (1,'Starter'),(2,'Growth'),(3,'Scale'),(4,'Legacy')", "INSERT INTO customers VALUES (1,'Atlas Co','Egypt',800),(2,'Northstar','UAE',1800),(3,'Blue Nile','Egypt',300),(4,'Cedar Labs','Lebanon',800),(5,'Sunrise','Jordan',300)", "INSERT INTO subscriptions VALUES (1,2,1,'2026-01-01',1,800),(2,3,2,'2026-01-15',1,1800),(3,1,3,'2026-01-01',1,300),(4,2,4,'2026-02-10',1,800),(5,1,5,'2026-02-22',1,300),(6,2,1,'2026-03-05',2,1600),(7,2,3,'2026-03-01',1,800)"],
                ['parent' => 'plans', 'parent_name' => 'plan', 'item' => 'customers', 'item_name' => 'customer', 'fact' => 'subscriptions', 'parent_fk' => 'plan_id', 'item_fk' => 'customer_id']),
            'inventory' => $this->dataset('Inventory', 'Suppliers, products, and stock movements.',
                ['suppliers' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', '']], 'products' => [['id', 'INTEGER', 'PK'], ['name', 'TEXT', ''], ['category', 'TEXT', ''], ['unit_price', 'DECIMAL', '']], 'movements' => [['id', 'INTEGER', 'PK'], ['supplier_id', 'INTEGER', 'FK'], ['product_id', 'INTEGER', 'FK'], ['event_date', 'DATE', ''], ['quantity', 'INTEGER', ''], ['value', 'DECIMAL', '']]],
                ['movements.supplier_id → suppliers.id', 'movements.product_id → products.id'],
                ['CREATE TABLE suppliers (id INTEGER PRIMARY KEY, name TEXT NOT NULL)', 'CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT NOT NULL, category TEXT, unit_price NUMERIC)', 'CREATE TABLE movements (id INTEGER PRIMARY KEY, supplier_id INTEGER, product_id INTEGER, event_date TEXT, quantity INTEGER, value NUMERIC)'],
                ["INSERT INTO suppliers VALUES (1,'TechSource'),(2,'Nile Office'),(3,'EuroParts'),(4,'Levant Supply')", "INSERT INTO products VALUES (1,'Wireless Mouse','Accessories',800),(2,'Keyboard','Accessories',1500),(3,'Office Chair','Furniture',4500),(4,'Monitor','Electronics',7200),(5,'USB Hub','Accessories',900)", "INSERT INTO movements VALUES (1,1,1,'2026-01-05',30,24000),(2,1,1,'2026-01-15',-6,-4800),(3,3,2,'2026-01-08',12,18000),(4,3,2,'2026-02-04',-5,-7500),(5,2,3,'2026-02-12',8,36000),(6,2,3,'2026-02-19',-5,-22500),(7,1,4,'2026-03-01',25,180000),(8,1,4,'2026-03-11',-7,-50400)"],
                ['parent' => 'suppliers', 'parent_name' => 'supplier', 'item' => 'products', 'item_name' => 'product', 'fact' => 'movements', 'parent_fk' => 'supplier_id', 'item_fk' => 'product_id']),
        ];
    }

    /** @return array<string, mixed> */
    public function find(string $key): array
    {
        return $this->all()[$key] ?? throw new InvalidArgumentException("Unknown dataset [{$key}].");
    }

    /** @param array<string, array<int, array<int, string>>> $tables @param array<int, string> $relations @param array<int, string> $schema @param array<int, string> $seed @param array<string, string> $map */
    private function dataset(string $name, string $description, array $tables, array $relations, array $schema, array $seed, array $map): array
    {
        return compact('name', 'description', 'tables', 'relations', 'schema', 'seed', 'map');
    }
}
