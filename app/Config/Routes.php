<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('ApiCreation');
$routes->setDefaultMethod('');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.


// routes for user registration
$routes->post('/user', 'RegisterController::register');// for register
$routes->get('/user', 'RegisterController::listing',['filter' => 'authFilter']);// for list all user
$routes->get('/user/(:num)', 'RegisterController::single/$1',['filter' => 'authFilter']); // for list single user
$routes->patch('/user/(:num)', 'RegisterController::update/$1',['filter' => 'authFilter']); // for updata data to sinle user
$routes->get('/user/verify/(:any)', 'RegisterController::verify/$1'); // for verify user email key
$routes->post('/user/forgotpassword', 'RegisterController::forgot');//for forgot password
$routes->get('/user/verifylink/(:any)/', 'RegisterController::resetlink/$1');//for verify reset password link
$routes->post('/user/resetpassword/', 'RegisterController::resetpassword');//for reset passsword
$routes->get('/user/fetchemail/(:any)', 'RegisterController::fetchemail/$1');//fetch email api load email address when user reset password



// routes for user login
$routes->post('/user/login', 'LoginController::index'); // for login
$routes->get('/user/resendlink/(:any)', 'LoginController::resendlink/$1'); //for resend verification link
$routes->get('/user/assign-project-list-for-single-user/(:num)', 'LoginController::getProjectListForSingleUser/$1',['filter' => 'authFilter']);//function for get all assign project list for single user for user not for admin with project status running 
$routes->get('/user/all-assign-project-list-for-single-user/(:num)', 'LoginController::getAllProjectListForSingleUser/$1',['filter' => 'authFilter']);//function for get all assign project list forsingle user it contain all project

$routes->delete('/user/delete/(:num)', 'LoginController::delete/$1',['filter' => 'authFilter']);//delete user from database
$routes->get('/user/count-project-list-for-single-user/(:num)', 'LoginController::countAllProjectForSingleUser/$1',['filter' => 'authFilter']);//function for get all assign project list count  for single user

$routes->get('/user/all-notifications/(:num)','LoginController::getNotifications/$1',['filter' => 'authFilter']);// function for get all notifications for login user
$routes->patch('/user/update-notifications/(:num)','LoginController::updateNotifications/$1',['filter' => 'authFilter']);// function for get all notifications for login user

$routes->post('/user/add-current-work','LoginController::addCurrentWorkDetails',['filter' => 'authFilter']);//function for Task assignment monitoring function for real-time updates on who is working on what.
$routes->get('/user/get-current-work-by-employee/(:num)','LoginController::getCurrentWorkDetails/$1',['filter' => 'authFilter']);//function for get real-time updates on who is working on what for the admin only....

$routes->get('/user/get-my-current-work/(:num)','LoginController::getMyCurrentWork/$1',['filter' => 'authFilter']);//function for get real-time work updates for employee

//routs for project


$routes->post('/project','ProjectController::addproject',['filter' => 'authFilter']); // for insert new project
$routes->get('/project','ProjectController::all_project',['filter' => 'authFilter']); // for list down all project
$routes->patch('/project','ProjectController::update',['filter' => 'authFilter']); // for update the existance project
$routes->delete('/project','ProjectController::delete',['filter' => 'authFilter']); // for delete the existance project
$routes->patch('/project/project-stage','ProjectController::update_project_stage',['filter' => 'authFilter']); // for update the project stage only for employee


// routes for time entry
$routes->post('/project/time','TimeController::addTimeEntry',['filter' => 'authFilter']); // for insert new project time entry
$routes->get('/project/list/(:any)/(:any)','TimeController::list/$1/$2',['filter' => 'authFilter']); // for fetch data for added all time entry for login user 
$routes->patch('/project/edit-time-entries','TimeController::EditExistingTimeEntries',['filter' => 'authFilter']);//function for reupdate the time entries after add
// routes for assign project to team 
$routes->post('/project/assign','ProjectAssignController::index',['filter' => 'authFilter']); // for assign a project to user
$routes->get('/project/assignlist/(:num)','ProjectAssignController::assignlist/$1',['filter' => 'authFilter']); // get all project assign list by current user
$routes->patch('/project/deallocate','ProjectAssignController::deallocate',['filter' => 'authFilter']);//for deallocating a project from user
$routes->post('/project/submit','ProjectAssignController::submit',['filter' => 'authFilter']);//for submit the project to approval by assigner when project completed from employee
// routes for generating reports
$routes->get('/reports/foruser/(:num)/(:num)','ReportsController::index/$1/$2',['filter' => 'authFilter']);//function for get reports for user all works and assign project with respect to provided user id 
$routes->get('/reports/for-user-time-entries/(:num)/(:num)/(:num)','ReportsController::getTimeEntries/$1/$2/$3',['filter' => 'authFilter']);//function for get all time entries for selected user and selected project id 
$routes->get('/reports/within-time-range/(:num)/(:any)/(:any)','ReportsController::getReportsWithInTimeRange/$1/$2/$3',['filter' => 'authFilter']); // function for get all works reports for all user within given range
$routes->get('/reports/all-user-list-on-single-project/(:num)/(:num)','ReportsController::getReportsForSingleProject/$1/$2',['filter' => 'authFilter']);// function for get all user list who have worked on or working on the perticular project


//routes for getting all holiday list with weekend
$routes->post('/add-new-holiday','HolidayListController::addHolidayList',['filter' => 'authFilter']);//function for get all holiday list
$routes->get('/holiday-list','HolidayListController::index',['filter' => 'authFilter']);//function for get all holiday list

// routes for assign task to in between employee
$routes->post('/task/assign-task-with-project','TaskController::assignTaskWithProject',['filter' => 'authFilter']);//function for assign a task to user with project 
$routes->post('/task/assign-task-without-project','TaskController::assignTaskWithoutProject',['filter' => 'authFilter']);//function for assign a task to user with project 
$routes->put('/task/revert-task/','TaskController::returnTaskToUser',['filter' => 'authFilter']);//function for revert task update to task assigner 
$routes->put('/task/update-task/(:num)/(:num)/(:any)','TaskController::updateTask/$1/$2/$3',['filter' => 'authFilter']);//function for update task only by task assigner  
$routes->get('/task/list/','TaskController::getTaskListByUserId',['filter' => 'authFilter']);//function for get task list for login user
$routes->get('/task/my-assign-task/(:num)','TaskController::getmyAssignTaskListByUserId/$1',['filter' => 'authFilter']);// function for get task assign list for login user(how much task he assign to other)

// routes for add comments for task
$routes->post('/task/comments','CommentsController::add_comments',['filter' => 'authFilter']);//function for add comments
$routes->get('/task/all-comments/(:num)/(:num)','CommentsController::allComments/$1/$2',['filter' => 'authFilter']);//function for get all comments for login user by task id
$routes->get('/task/all-comments-for-task-assigner/(:num)/(:num)','CommentsController::comments_on_perticular_task/$1/$2',['filter' => 'authFilter']); // function for get all comments on particular task for task assigner(who assign a task also get comment list)

// routes for get email conversation for project in between employee and project assigner
$routes->get('/email/get-email-list/(:num)','EmailConversationController::email_list/$1',['filter' => 'authFilter']); // function for get all email list


// routes for cron job
$routes->get('/cron-jobs','CronJobs::deleteAllTaskForCurrentDate'); // function for get all email list
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
