<?php

use Vendor\Schoolarsystem\Core\Middleware\AuthMiddleware;

// Carreers Api Modal Routes
$router->post('/carreer/modal/careersSubjects', [Vendor\Schoolarsystem\Controllers\CarreersController::class, 'careersSubjectsModal'], [AuthMiddleware::class]);

// Grades Api Routes
$router->get('/api/getMakeOverDetails', [Vendor\Schoolarsystem\Controllers\GradesController::class, 'getMakeOverGrades'], [AuthMiddleware::class]);
$router->post('/api/addMakeOverGrade', [Vendor\Schoolarsystem\Controllers\GradesController::class, 'addMakeOverGrade'], [AuthMiddleware::class]);
// Grades Api Modal Routes
$router->post('/api/modal/makeOverExam', [Vendor\Schoolarsystem\Controllers\GradesController::class, 'makeOverExamModal'], [AuthMiddleware::class]);
$router->post('/api/modal/viewMakeOver', [Vendor\Schoolarsystem\Controllers\GradesController::class, 'viewMakeOverModal'], [AuthMiddleware::class]);

// Groups Api Routes
$router->get('/api/getNoGroupStudentsList', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getNoGroupStudentsList'], [AuthMiddleware::class]); // ✅
$router->post('/api/getGroupCareer', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getGroupCareer'], [AuthMiddleware::class]); // ✅
$router->get('/api/getGroupSchedules', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getGroupSchedules'], [AuthMiddleware::class]); // ✅
$router->post('/api/addSchedule', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'addSchedule'], [AuthMiddleware::class]);
// Groups Api Modal Routes
$router->post('/api/modal/groupsEdit', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'groupsEditModal'], [AuthMiddleware::class]);

// Practical Hours Api Routes
$router->get('/api/getEventDetails', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'getEventDetails'], [AuthMiddleware::class]);
$router->get('/api/getStudentsHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'studentsHours'], [AuthMiddleware::class]);
$router->get('/api/getStudentlHoursData', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'getStudentlHoursData'], [AuthMiddleware::class]);
$router->post('/api/addEvent', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'addEvent'], [AuthMiddleware::class]);
$router->post('/api/confirmHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'confirmHours'], [AuthMiddleware::class]);
$router->post('/api/addStudentHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'addStudentHours'], [AuthMiddleware::class]);
$router->post('/api/deleteEvent', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'deleteEvent'], [AuthMiddleware::class]);
$router->post('/api/deleteHour', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'deleteHour'], [AuthMiddleware::class]);
// Practical Hours Api Modal Routes
$router->post('/api/modal/addEvent', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'addEventModal'], [AuthMiddleware::class]);
$router->post('/api/modal/eventDetails', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'eventDetailsModal'], [AuthMiddleware::class]);
$router->post('/api/modal/addHours', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'addHoursModal'], [AuthMiddleware::class]);
$router->post('/api/modal/seeTotal', [Vendor\Schoolarsystem\Controllers\PracticalHoursController::class, 'seeTotalModal'], [AuthMiddleware::class]);

// Students Api Routes
$router->post('/api/updateStatus', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'updateStatus'], [AuthMiddleware::class]);
$router->get('/api/getStudentsListSelect', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentsListSelect'], [AuthMiddleware::class]);
$router->get('/api/getStudentName', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentName'], [AuthMiddleware::class]);
// Students Api Modal Routes
$router->post('/api/modal/studentStatus', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'studentStatusModal'], [AuthMiddleware::class]);

// Subjects Api Routes
$router->post('/api/getSubjectsListSelect', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getSubjectsListSelect'], [AuthMiddleware::class]);
$router->post('/api/getChildSubject', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getChildSubject'], [AuthMiddleware::class]);
$router->get('/api/subjectsListTable', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'subjectsListTable'], [AuthMiddleware::class]);
$router->post('/api/addSubjectCareer', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'addSubjectCareer'], [AuthMiddleware::class]);
