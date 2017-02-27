# storageDesu OwO

storageDesu is a little project about a storage server where files aren't referenced on a list ( you need the link to access a file ) are automatically deleted 1 week after they were last accessed.

Basically, this is just a fork of [QuadFile](https://github.com/QuadPiece/QuadFile) rewritten in PHP

It's not complete, for now it only uploads the file to the server and registers it to the database. I still have to do the routing of URLs and the automatic cleaner which will be running everytime to delete old files.

The whole code you see here ( or the one in the first commit if you come from the future ) was written in less than an hour in a tired rush between 2am and 3am

## Requirements

* Apache and PHP ( idk if it works with Nginx)
* MySQL
* Root access on the server to change permissions

## Installation

#### 1. Download the repo

```shell
git clone https://github.com/Rominou34/storageDesu.git
```

#### 2. Create the database

Go into PHPmyadmin and create a new database with whatever name you want

Then import `database.sql` and it will automatically create the table for you

#### 3. Configure

Crate a file named `config.php` based on the template of `config.php.sample`

Change the values according to your case and how you want the website to work

#### 4. Create the files folder

Create a folder with whatever name you want ( default name is `files/` so change the config if you put a different one ):

```shell
mkdir files
```

In order to let users upload their files, change the permissions of the folder where the files will be uploaded:
```shell
chmod 777 files
```

#### 5. Make sure everything is good with Apache

* Make sure rewrite is enabled or else the URLs for the files won't work
    * When I installed it on my server it didn't work because Apache didn't have rewrite enabled so make sure to enable the module and enable it in your VirtualHost.
* Make sure your server accepts big files
  * Make sure php.ini allows 128MB files ( if you keep the default config ), both in upload and in post

#### 6. Enjoy

It currently works on my server but if something were to break on yours, please open an issue and I'll try to fix that
