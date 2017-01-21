# veikt.com

  1. **download** 

 The code downloads job postings from around the internet and saves them into separate files of web server. This is the cron job.
 
 At the moment the project downloads info from: 
 - www.cvbankas.lt
 - more is coming soon.


  2. **normalize** 
  
  This code walks through files (results) of step1 and analyzes them semantically, unites between languages, translates, and does much more. After step2 is finished, we have files (results) of step1 put into different tables and columns of the project database (currently MySQL).
  
  3. **publicize** 
  
  This code publish and unpublish jobs.
 
  4. **www** 
  
  This is the website that outputs the results of download, normalize, publicize to public in the form of website. Content filters and much more is comming to be developed soon.



# veikt.com (for all developers)

```
For those of you who wish to contribute to any of steps mentioned - there is the Vagrant-based development box. This have to make you involved in minutes:
https://github.com/sugalvojau/veikt.dev
```


## veikt.com (for www developers)

There are two branches at the moment: master and symfony. Master is currently moved to support Laravel 5 PHP framework. Symfony is left in the separate branch for historical reasons, but if the branch would become active one day then anything may happen... At the moment I am interested in getting deeper into Laravel and so I turn master into Laravel.
 
**Some commands to remember for Symfony developers:**

 `php artisan`
 
**Some commands to remember for Symfony developers:**

`php bin/console doctrine:database:drop --force`

`php bin/console doctrine:database:create`

`php bin/console doctrine:migration:diff`

`php bin/console doctrine:migration:migrate`

`php bin/console doctrine:fixtures:load`

****

If something goes wrong with executing shell scripts then check for line endings inside executable shell scripts! Must be LF. Some software may change line endings without you noticing this: git management tools, IDE, others.


```
Feel free to contact if any questions arise.
``` 

