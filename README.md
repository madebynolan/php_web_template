# PHP Web Template
### By Nolan Nicholson

I was learning PHP and needed a template for my friends websites, so I came up with this.

It allows user signup / login, has email functionality for 2FA and Password Resets, some sql schemas, and a decent set of home brewed CSS / JS. It's not entirely complete as I'm writing this now, but it's most of the way there. I believe all security measures at the moment are sound, but please feel free to point out any flaws you find in the code, or my repo to help me learn and get better :)

## Requirements
You don't need much for this project, but it's always good to double check.
### Windows (kinda)
I use Windows, so the following instructions will outline installation for Windows users. I'm sure you Linux users will figure it out o/

### PHP
I'm using v8.5 but this may work fine on older editions.
Download: [PHP v8.5](https://windows.php.net/downloads/releases/php-8.4.12-Win32-vs17-x64.zip) 

### AMP Stack
Personally, I perefer [Laragon](https://laragon.org/download). This one has proven to be much more stable than XAMPP.
  a) Once installed, click the settings (gear) icon in the top right corner. Change the route under `Document Root` to the **public** folder with the project clone. Close the window and start up the Apache server.
  b) Right click anywhere inside the Laragon interface. Under Tools > Quick Add, select `phpMyAdmin`. After phpMyAdmin installs, change the default timezone. Currently, my hosting provider defaults to `"-04:00"` / `America/New_York` so I've set PHP to do the same. Right click anywhere inside the Laragon interface. Under MySQL, select `my.ini`. Within the mysqld section, paste this line `default-time-zone="-04:00"`. Save the file, close the window and start up MySQL.

### Composer
This is a dependency manager for PHP. It let's you use cool libraries!
Download: [Composer v2.8.12](https://getcomposer.org/Composer-Setup.exe)

## Getting Started

1. Clone the repo
Open a terminal and run the following commands, with your desired clone path in the first command
```bash
cd C:\your\directory\here && ^
git clone https://github.com/madebynolan/php_web_template && ^
cd php_web_template && ^
composer install
```

2. Create the database
  a) Open the cloned project folder. Within the sql folder, open `main.sql`. Change *database_name* to whatever you'd like to call the database, or simply leave it as is. Select everything and copy.
  b) Open your browser to `localhost/phpmyadmin`.
  c) Under the `SQL` tab, paste the copied sql and press **Go** at the bottom.
  d) Head to the `User Accounts` tab and click `Add User Account`. Set the `User name` to whatever you'd like. Change `Host name` to Local in the drop down menu. Set a strong password, it's a good habit. In the `Global Privileges` only check the boxes for `SELECT, INSERT, UPDATE, DELETE`. Once you're done, press **Go** at the bottom.

3. Set Environment Variables
  a) Rename the `template.env` file to just `.env`.
  b) Inside, fill out the fields with your own credentials
  - For the MySQL section, use the credentials for the User you just created in the last step, along with the chosen database name in the sql schema.
  - For the Email section...


