## Jukebox

"jukebox" is my take on iTunes.

Displays songs and artists and lyrics, enables a user to create and play playlists.

This predicates you have composer.

Then clone this repository, cd into the root directory, and run composer install.

Create a mysql database.

Copy env.example to .env  
Run php artisan key:generate to populate the APP_KEY in the .env file.  
Set DB credentials and other credentials in the .env file.

The device type and location of the music directory may also need to be updated in the .env file  
+ LOCAL_PARTITION - will need to be set to the device type partition (see config/filesystems.php)
+ MEDIA_DIRECTORY - will need to be set to the location of the music folder from the root directory

In order to run the Words command, external Word databases will need to be imported:
I am currently using two different Word databases, both have words the other does not.

The first is Princeton University's WordNet database.  
To install:  
Unzip storage/backups/wordnet20-from-prolog-all-3.zip  
Edit the sql file changing all TYPE=MyISAM to ENGINE=MyISAM  
Load the sql into your mysql database.  
mysql -u *user* -p*userpassword* jukebox < storage/backups/wordnet20-from-prolog-all-3.sql

The second is sourced from a Source Forge project - https://sourceforge.net/projects/mysqlenglishdictionary/  
Load the sql and create the database with storage/backups/englishdictionary.sql  
mysql -u *user* -p*userpassword* jukebox < storage/backups/englishdictionary.sql

Run database migrate scripts - php artisan migrate

Update database with existing data - storage/backups/mymusic.sql  
The songs location string uses the macOS-style directory separator.  This will need to be mass updated to run on a Windows device.
This will be refactored and unnecessary in a future release.

php artisan serve  

__Testing__

This application is using Codeception for testing. 
A WebDriver needs to be setup up for the tests to run - https://codeception.com/docs/modules/WebDriver

Copy .env to *.env.testing*  
Add the following properties to enable the test suite to log into the application  
+ TEST_EMAIL
+ TEST_PW

I am using the local ChromeDriver approach.   
Running *chromedriver --url-base=/wd/hub*  
The WebDriver port in acceptance.suite.yml needs to be set to the port the chromedriver is running    
Then running the tests *php vendor/bin/codecept run acceptance*  
A single test can be run with *php vendor/bin/codecept run acceptance SongsCest:tryViewSongEditPage*  

