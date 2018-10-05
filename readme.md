1. Download all the files from this project.
2. To install Homestead directly into the project, require it using Composer:
   go to the project folder run:
   
   composer require laravel/homestead --dev
3. vendor\\bin\\homestead make
4. cp .env.example .env
5. php artisan key:generate
6. vagrant up
7. On Windows, open file  C:\Windows\System32\drivers\etc\hosts. 
   add one line in the end:
   192.168.10.10  homestead.test
   
   When you finish, you can see the result in browse. http://homestead.test/ 
