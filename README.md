<p align="center"><a href="https://www.studocu.com/en-us" target="_blank"><img src="https://upload.wikimedia.org/wikipedia/commons/e/ec/Logo_StuDocu_Wikipedia.png" width="400"></a></p>

## STUDOCU CLI Q&A Project

This project provides an interactive CLI Questions and Answers APP.

To run this project:

- Get the project on your machine <br /><br />
- Make sure that you create a .env file, if you copy the content of the .env.example, make sure that you have a database called: "laravel" or change the database credentials on the .env file <br /><br />
- Make sure that you have a DB server running on the database credentials that you provide. <br /><br />
- Inside the project's folder run: <br />
  <strong>composer install</strong><br /><br />
- Inside the project's folder run the migrations with the command: <br />
  <strong>php artisan migrate</strong><br /><br />
- Inside the project's folder run: <br />
  <strong><i>php artisan qanda:interactive</i></strong>
  
Tip: if you have some ANSI issues on your CLI, you can run: <br /><br />
<strong><i>php artisan qanda:interactive --no-ansi</i></strong>

<br />
<br />

Notes: 
- The program will ask you to provide a nickname before you
access the menu, this nickname is for the application to understand
who are you and retrieve your questions and statistics, you can 
include as many users as you want, whenever you provide a nickname 
that doesn't exist yet, the app will create a new user and
you can use this nickname whenever you want.
    

