<?php

use Vendor\Schoolarsystem\Core\Middleware\AuthMiddleware;

// Grades Api Routes
$router->get('/api/getMakeOverDetails', [Vendor\Schoolarsystem\Controllers\GradesController::class, 'getMakeOverGrades'], [AuthMiddleware::class]);
$router->post('/api/addMakeOverGrade', [Vendor\Schoolarsystem\Controllers\GradesController::class, 'addMakeOverGrade'], [AuthMiddleware::class]);

// Groups Api Routes
$router->get('/api/getNoGroupStudentsList', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getNoGroupStudentsList'], [AuthMiddleware::class]); // ✅
$router->post('/api/getGroupCareer', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getGroupCareer'], [AuthMiddleware::class]); // ✅
$router->get('/api/getGroupSchedules', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getGroupSchedules'], [AuthMiddleware::class]); // ✅
$router->post('/api/addSchedule', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'addSchedule'], [AuthMiddleware::class]);

// Practical Hours Api Routes
$router->get('/api/getEventDetails', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'getEventDetails'], [AuthMiddleware::class]);
$router->get('/api/getStudentsHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'studentsHours'], [AuthMiddleware::class]);
$router->get('/api/getStudentlHoursData', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'getStudentlHoursData'], [AuthMiddleware::class]);
$router->post('/api/addEvent', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'addEvent'], [AuthMiddleware::class]);
$router->post('/api/confirmHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'confirmHours'], [AuthMiddleware::class]);
$router->post('/api/addStudentHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'addStudentHours'], [AuthMiddleware::class]);
$router->post('/api/deleteEvent', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'deleteEvent'], [AuthMiddleware::class]);
$router->post('/api/deleteHour', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'deleteHour'], [AuthMiddleware::class]);

// Students Api Routes
$router->post('/api/updateStatus', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'updateStatus'], [AuthMiddleware::class]);
$router->get('/api/getStudentsListSelect', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentsListSelect'], [AuthMiddleware::class]);
$router->get('/api/getStudentName', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentName'], [AuthMiddleware::class]);

// Subjects Api Routes
$router->post('/api/getSubjectsListSelect', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getSubjectsListSelect'], [AuthMiddleware::class]);
$router->post('/api/getChildSubject', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getChildSubject'], [AuthMiddleware::class]);
$router->get('/api/subjectsListTable', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'subjectsListTable'], [AuthMiddleware::class]);
$router->post('/api/addSubjectCareer', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'addSubjectCareer'], [AuthMiddleware::class]);
