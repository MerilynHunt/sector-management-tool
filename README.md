# Sector Management Project

This is a full-stack web application using **React** (Vite), **PHP**, **Tailwind CSS**, and **MariaDB** 
It allows users to submit their name, select sectors they're involved in, and agree to terms. 
Sector options are imported from a predefined HTML list and stored in a database with the proper hierarchical relationships.

---

## Features

- React-based frontend using Tailwind CSS for styling
- PHP backend API for form handling and database interaction
- Sector list imported from HTML with auto-detected hierarchy
- Data stored in a MariaDB database
- Sessions to allow users to edit their own data

---

## Setup instructions
### 1. Install XAMPP

Download and install XAMPP:
https://www.apachefriends.org/

Make sure the following components are running:
- Apache
- MySQL

### 2. Clone the repository to a local folder
- https://docs.github.com/en/repositories/creating-and-managing-repositories/cloning-a-repository
- Move the **import_sectors_to_db.php** and **sectors.html** files from the backend folder to a XAMPP htdocs new folder

---

### 3. Set Up the Database

#### Step 1: Open phpMyAdmin  
Visit: http://localhost/phpmyadmin

#### Step 2: Create the Database  
Copy the code from the **sql/schema.sql** file to phpmyadmin and run it

#### Step 3: Import Sectors  
In the terminal or browser, run the sector import script after replacing the folder name:
http://localhost/xampp_folder_name/import_sectors_to_db.php
> This script reads the `sectors.html` file, parses the sector `<option>` tags, detects their hierarchy via indentation, and inserts them into the database.

---

### 4. Configure Your Database Credentials
Edit the sample config file in the backend folder to your database credentials and rename the file to **db_config.php**

### 5. Start the frontend
```
cd frontend
npm install
npm start
```

### 6. Visit the localhost address used by React
Fill out the name input, select sectors from the list, agree to the terms and click save
> The data is then sent to the database

---

## Project structure
```
sector-management-project/
├── backend/                        # PHP backend
│   ├── api/                        # API endpoints
│   ├── sectors.html                # HTML source file
│   ├── import_sectors_to_db.php    # Parses and inserts sectors to db
│   └── db_config.sample.php        # Example config file
├── frontend/                       # React frontend with Tailwind CSS
│   ├── public/
│   ├── src/
│   │   ├── App.css                 # Additional styling file
│   │   ├── App.jsx                 # Main React component
│   │   ├── index.css               # Tailwind import
│   │   └── main.jsx                # App mounting file
│   ├── package.json                # Frontend dependencies
│   ├── tailwind.config.js          # Tailwind configuration
│   ├── vite.config.js              # Vite configuration
│   ├── .gitignore
│   └── index.html                  # Entry point to frontend
├── sql/
│   └── schema.sql                  # Full DB schema
├── .gitignore
└── README.md
```
