<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Task Management Application - Gateway Assignment.

The application is divided into two separate API groups, the dashboard and the user, the idea is the dashboard is meant to be for the manager of the application (project owner in current context).
And the user group is for the end user.

The APIs created in this application are based on the requirements that are found in the root directory of the project in the format of PDF, this application strictly follows that guideline and any necessary function that is missing, It's because it did not exist in the guideline.

Installation (without docker):
- Clone the repo at https://github.com/Ravensborn/task-management.
- Install dependencies via composer: `composer install`
- Duplicate and rename `env.local` to `.env` and edit as necessary.
- Run the following artisan commands, `php artisan migrate:fresh --seed`, `php artisan storage:link`.

Installation (with docker)
- Laravel sail is installed, edit as necessary and run.

Postman Collection
- Can is included in the root of the project.
- Or can be found here https://documenter.getpostman.com/view/18062098/2s93Jus2Su (this is recommended to use as it holds the environment variables).
- The postman collection comes with an environment file, make sure that one is selected, update the `host` value as needed if you decide to run it locally and when you generate a token it will automatically save it in the environment for you, then you can either navigate to dashboard folder or user folder to start making requests to the application.


Notes:
- The application is live at <a href="task-management.rozhapp.com">glitter.rozhapp.com</a>.
- The api response format follows the <a href="https://github.com/omniti-labs/jsend">JSend</a> standards, I found that the most suitable for this project.
- The dashboard group serves the purpose of managing all the models in the application, In this section the restrictions of the guideline have not been applied to make it to manageable by admins.
- The user group servers the purpose of users ability to see and manage only their tasks, the rules in the guidelines are applied here.
- The first user is `yad@example.com` with password of `password`, it have the permissions of `product owner`.
- An example of users csv file is included in the project root directory for testing the batch user import.
