# veikt.com

Regular internet users see 1/4 part of the project - [veikt.com](www.veikt.com) website only.  
However, the project consists of a lot more than just the website.
 
 1. **download** 

 The code downloads job postings from around the internet and saves them into separate files of the web server. This is the cron job.
 
 At the moment the project downloads info from: 
  - www.cvbankas.lt
  - more is coming soon.

 2. **normalize** 
  
  This code walks through files (results) of step1 and analyzes them semantically, unites between languages, translates, and does much more. After step2 is finished, we have files (results) of step1 put into different tables and columns of the project database (currently MySQL).
  
 3. **publicize** 
  
  This code publish and unpublish jobs inside our system.
 
 4. **www** 
  
  This is the website that outputs the results of download, normalize, publicize to public in the form of website.



## Are you the developer?

For those who wish to contribute to any of steps mentioned - there is the Vagrant-based development box.  
Go here: [https://github.com/sugalvojau/veikt.dev](https://github.com/sugalvojau/veikt.dev)


## More Info:

[Contact me](http://portfolio.vertyb.es/) if any difficulties arise.
