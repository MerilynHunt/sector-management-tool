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
- Sessions to allow users to edit and delete their own data

![final_diagram](https://github.com/user-attachments/assets/41e5f373-39d3-4705-8dfd-9633f0a25484)

---

## Setup instructions
### 1. Install XAMPP

Download and install XAMPP:
https://www.apachefriends.org/

Make sure the following components are running:
- Apache
- MySQL

### 2. Clone the repository and set up backend
- https://docs.github.com/en/repositories/creating-and-managing-repositories/cloning-a-repository
- Move the **backend** folder from the project folder to the XAMPP htdocs folder

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
If you wish to delete submitted data then during the active session disagree to the terms and the data will be deleted
