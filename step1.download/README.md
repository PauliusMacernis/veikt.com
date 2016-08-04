# step1.download
This downloads the job postings and puts them into separate files:

`projects/{project_name_of_yours}/posts/{post_id_known_by_source}/url` -- this is the text file that contains url of a job post

`projects/{project_name_of_yours}/posts/{post_id_known_by_source}/html` -- this is the text file that contains html of a job post

...creating any other files are optional, for example:

`projects/{project_name_of_yours}/posts/{post_id_known_by_source}/statistics` -- this is the text file that contains html of a job post statistics (for example: count of page views, count of applicants, etc.)


The file named `projects/{project_name_of_yours}/entrance.sh` is the main invokable file of your project. This file is invoked by the main program (global scope deciding which project to update and which to not). Actually, this kind of architecture allows you to choose many tools (and programming languages) for downloading the content of job postings to the web server.


# Projects needs to be added: #
01.net
Accounting.com
AccountWorld.com
American Economic Association
American Finance Association
American Securitization Forum
America's Job Bank
Association for Financial Professionals
Association of Business Economists
Association of Corporate Counsel
Association of Investment Management
Association of Real Estate Women
Black Data Processing Associates
Bloomberg
Brokerhunter.com
Cadremploi
Cadresonline
Capital Markets Credit Analysts
Career Builder
Careerjournal.com
CareerTimes
CFAI
Challenger, Gray, &amp; Christmas
CityJobs
CJOL
Corriere Dellasera
Craigslist
creditjob.com
CREW.org
DBM Jobscout
Dice
Econ Jobs
eFinancial Careers
FAZ
FIASI
Financial Times
Financial times Germany
FINS.com
Frankfurter Allgemeine Zeitun
GAAPJobs.com
Global Association Risk Professionals
HotJobs
Indeed
Japan Times
Japan Times Job
Job Finance
Jobpilot.de
jobscentral.com.sg
jobscout24.de
Jobsdb
Jobserve
jobstreet.com
Journal of Finance
Keljob
La Stampa
Ladder.com
Latinos in Information Science
Le Monde
Lee Hecht Harrison
LinkedIn
magny.org
Maths-fi
MBA Focus
Meetingjobs.com
Monster
MonsterTrak
Moody's Alumni Network
Moodys Alumni Website
Moodys.com
Moscow Times
Nacelink
New York Society of Security
New York Times
Nikkei Net
Nikkei News
NY State Bar Association
One Wire
Proactive Approach
Quan Finance.com
Recu-Nabi
S1jobs
San Francisco Chronicle
seek.com.au
SelectLeader.com
Selectleaders
Society for Human Resources Managers
South China Morning Post (SCMP)
Star Newspaper
stellenanzeigen.de
stepstone.de
Straits Times
TaxTalent
The Australia Financial Review
The Australian
The Ayers Group
The Guardian
The Professional Risk Manager
The Sydney Morning Herald
Twitter
Vault.com
Vedemosti
Wall Street Journal
Wallstreetoasis.com
Women in Information Technology
www.51job.com
www.chinahr.com
www.cjol.com
www.headhunter.ru
www.newsmth.net
www.yingjiesheng.com
XING
Ying Jiesheng
Zhaopin.com
...and more
