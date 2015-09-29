# step1.download
This downloads the job postings and puts them into separate files:

`projects/{project_name_of_yours}/posts/{post_id_known_by_source}/url` -- this is the text file that contains url of the job post

`projects/{project_name_of_yours}/posts/{post_id_known_by_source}/html` -- this is the text file that contains html of the job post

...creating any other files are optional, for example:

`projects/{project_name_of_yours}/posts/{post_id_known_by_source}/statistics` -- this is the text file that contains html of the post statistics (for example: count of page views, count of applicants, etc.)


The file named `projects/{project_name_of_yours}/entrance.sh` is the main invokable file of the source. This file is invoked by the main program (global scope deciding what to get and what to not). Actually, this kind of architecture allows you to choose many tools (and programming languages) for downloading the content to the web server.


