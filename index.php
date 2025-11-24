<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e11570;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--light-gray);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
            transform: translateY(-2px);
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border-radius: var(--border-radius);
            padding: 8px 15px;
            box-shadow: var(--box-shadow);
            flex-grow: 1;
            max-width: 400px;
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 8px;
            width: 100%;
            font-size: 1rem;
        }

        .search-box i {
            color: var(--gray);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        thead {
            background-color: var(--light-gray);
        }

        th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--light-gray);
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid var(--light-gray);
        }

        tr {
            transition: var(--transition);
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .student-id {
            font-weight: 600;
            color: var(--primary);
        }

        .student-name {
            font-weight: 500;
        }

        .action-cell {
            display: flex;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
        }

        footer {
            text-align: center;
            margin-top: 3rem;
            padding: 1.5rem;
            color: var(--gray);
            font-size: 0.9rem;
            border-top: 1px solid var(--light-gray);
        }

        /* Form Styles */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--secondary);
        }

        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            th, td {
                padding: 12px 8px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Student Management System</h1>
            <p class="subtitle">Easily manage student records and information</p>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Student
                </a>
                
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search students...">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Sample data for demonstration - replace with your actual PHP code
                    // $students = [
                    //     ['id' => 1001, 'name' => 'Alex Johnson'],
                    //     ['id' => 1002, 'name' => 'Maria Garcia'],
                    //     ['id' => 1003, 'name' => 'James Wilson'],
                    //     ['id' => 1004, 'name' => 'Sarah Miller'],
                    //     ['id' => 1005, 'name' => 'David Chen']
                    // ];
                    
                    // Replace with your actual PHP code:
                        include 'db.php';
                        $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>
                                    <td class='student-id'>{$row['id']}</td>
                                    <td class='student-name'>{$row['name']}</td>
                                    <td class='action-cell'>
                                        <a href='edit.php?id={$row['id']}' class='btn btn-yellow'>
                                            <i class='fas fa-edit'></i> Edit
                                        </a>
                                        <a href='delete.php?id={$row['id']}' class='btn btn-danger'>
                                            <i class='fas fa-trash'></i> Delete
                                            
                                        </a>
                                    </td>
                                </tr>";
                        }
                    
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>Student Management System &copy; <?php echo date('Y'); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Simple search functionality
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('.student-name').textContent.toLowerCase();
                const id = row.querySelector('.student-id').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || id.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>