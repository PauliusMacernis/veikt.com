# veikt.com

  1. **step1.download** 

 The code downloads job postings from around the internet and saves them into separate files of web server. This is the cron job.
 
 At the moment the project downloads info from: 
 - www.cvbankas.lt
 - more?


  2. **step2.normalize** 
  
  This code walks throw files (results) of step1 and analyzes them semanticaly, unites between languages, translates, and does much more. After step2 is finished, we have files (results) of step1 put into different tables and columns of the project database (currently MySQL).
 
  3. **step3.output** 
  
  This is the website that outputs the results of step1 and step2 to public. Content filters and much more is comming to be developed soon.


```
For those who wish to contribute there is a dev box. This have to make you involved in minutes:
https://github.com/sugalvojau/veikt.dev
```

**Some commands to remember (useful for step3 development):**

`bin/console doctrine:database:drop --force`

`bin/console doctrine:database:create`

`bin/console doctrine:migration:diff`

`bin/console doctrine:migration:migrate`

`bin/console doctrine:fixtures:load`


```
Feel free to contact if any questions arise.
```


p.s. If something goes wrong with executing shell scripts then check for line endings inside executable shell scripts! Must be LF. Some software may change line endings without you noticing this: git management tools, IDE, others.

