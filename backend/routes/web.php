<?php

use Vendor\Schoolarsystem\Core\Middleware\AuthMiddleware;

$router->get('/', [Vendor\Schoolarsystem\Controllers\LoginController::class, 'index']);

// Admissions Routes ✅ COMPLETE
$router->get('/admission/getAllNewAdmissions', [Vendor\Schoolarsystem\Controllers\AdmissionsController::class, 'getAllNewAdmissions'], [AuthMiddleware::class]);
$router->post('/admission/deleteAdmission', [Vendor\Schoolarsystem\Controllers\AdmissionsController::class, 'deleteAdmission'], [AuthMiddleware::class]);

// Carreers Routes ✅ COMPLETE
$router->get('/carreer/getCarreerById', [Vendor\Schoolarsystem\Controllers\CarreersController::class, 'getCarreerById'], [AuthMiddleware::class]);
$router->get('/carreer/getAllCarreers', [Vendor\Schoolarsystem\Controllers\CarreersController::class, 'getAllCarreers'], [AuthMiddleware::class]);
$router->post('/carreer/addCarreer', [Vendor\Schoolarsystem\Controllers\CarreersController::class, 'addCarreer'], [AuthMiddleware::class]);
$router->post('/carreer/updateCarreer', [Vendor\Schoolarsystem\Controllers\CarreersController::class, 'updateCarreer'], [AuthMiddleware::class]);
$router->post('/carreer/deleteCarreerById', [Vendor\Schoolarsystem\Controllers\CarreersController::class, 'deleteCarreerById'], [AuthMiddleware::class]);

// Groups Routes ✅ COMPLETE
$router->get('/group/getGroupById', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getGroupById'], [AuthMiddleware::class]);
$router->get('/group/getAllGroups', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getAllGroups'], [AuthMiddleware::class]);
$router->post('/group/addGroup', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'addGroup'], [AuthMiddleware::class]);
$router->post('/group/updateGroup', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'updateGroup'], [AuthMiddleware::class]);
$router->post('/group/deleteGroupById', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'deleteGroupById'], [AuthMiddleware::class]);
$router->get('/group/getStudentsByGroupId', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getStudentsByGroupId'], [AuthMiddleware::class]);
$router->post('/group/addStudentToGroup', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'addStudentToGroup'], [AuthMiddleware::class]);
$router->post('/group/removeStudentFromGroup', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'removeStudentFromGroup'], [AuthMiddleware::class]);
$router->get('/group/getCarreersForGroupCreation', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getCarreersForGroupCreation'], [AuthMiddleware::class]);
$router->get('/group/getDuplicateStudents', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getDuplicateStudents'], [AuthMiddleware::class]);
$router->get('/group/getStudentDuplicateGroups', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'getStudentDuplicateGroups'], [AuthMiddleware::class]);
$router->post('/group/resolveDuplicate', [Vendor\Schoolarsystem\Controllers\GroupsController::class, 'resolveDuplicate'], [AuthMiddleware::class]);

// Login Routes ✅ COMPLETE
$router->post('/login', [Vendor\Schoolarsystem\Controllers\LoginController::class, 'login']);
$router->post('/logout', [Vendor\Schoolarsystem\Controllers\LoginController::class, 'logout']);
$router->get('/auth/microsoft/reauth', [Vendor\Schoolarsystem\Controllers\LoginController::class, 'startMicrosoftReauthentication'], [AuthMiddleware::class]);

// Payments Routes ⚠️ CHECK
$router->get('/payment/getPaymentHistory', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'getPaymentHistory'], [AuthMiddleware::class]);
$router->post('/payment/getPaymentById', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'getPaymentById'], [AuthMiddleware::class]);
$router->post('/payment/addPayment', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'addPayment'], [AuthMiddleware::class]);
$router->post('/payment/updatePayment', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'updatePayment'], [AuthMiddleware::class]);
$router->post('/payment/deletePaymentById', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'deletePaymentById'], [AuthMiddleware::class]);
$router->post('/payment/cancelPaymentById', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'cancelPaymentById'], [AuthMiddleware::class]);
$router->post('/payment/verifyPassword', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'verifyPassword'], [AuthMiddleware::class]);
$router->get('/payment/verifyTaxData', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'verifyTaxData'], [AuthMiddleware::class]);
$router->post('/payment/getStudentsPayMount', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'getStudentsPayMount'], [AuthMiddleware::class]);
$router->post('/payment/setStudentPayMount', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'setStudentPayMount'], [AuthMiddleware::class]);
$router->post('/payment/savePaymentDays', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'savePaymentDays'], [AuthMiddleware::class]);
$router->post('/payment/verifyMonthlyPayment', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'verifyMonthlyPayment'], [AuthMiddleware::class]);
$router->post('/payment/checkIfPaymentMade', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'checkIfPaymentMade'], [AuthMiddleware::class]);
$router->post('/payment/sendPaymentReceipt', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'sendPaymentReceipt'], [AuthMiddleware::class]);
$router->post('/payment/sendPaymentByEmail', [Vendor\Schoolarsystem\Controllers\PaymentsController::class, 'sendPaymentByEmail'], [AuthMiddleware::class]);

// Students Routes
$router->get('/student/getStudentById', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentById'], [AuthMiddleware::class]);
$router->get('/student/getAllStudents', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getAllStudents'], [AuthMiddleware::class]);
$router->post('/student/addStudent', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'addStudent'], [AuthMiddleware::class]);
$router->post('/student/updateStudent', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'updateStudent'], [AuthMiddleware::class]);
$router->post('/student/deleteStudentById', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'deleteStudentById'], [AuthMiddleware::class]);
$router->get('/student/getAllStudentsUsers', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getAllStudentsUsers'], [AuthMiddleware::class]);
$router->post('/student/addStudentUser', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'addStudentUser'], [AuthMiddleware::class]);
$router->post('/student/updateStudentUser', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'updateStudentUser'], [AuthMiddleware::class]);
$router->post('/student/desactivateStudentUser', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'desactivateStudentUser'], [AuthMiddleware::class]);
$router->post('/student/reactivateStudentUser', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'reactivateStudentUser'], [AuthMiddleware::class]);
$router->get('/student/getStudentsMicrosoftUsers', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getMicrosoftStudentsUsers'], [AuthMiddleware::class]);
$router->get('/student/findMicrosoftUser', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'findMicrosoftUser'], [AuthMiddleware::class]);
$router->post('/student/assignMicrosoftUserToStudent', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'assignMicrosoftUserToStudent'], [AuthMiddleware::class]);
$router->get('/student/verifyStudentToken', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'verifyStudentToken'], [AuthMiddleware::class]);
$router->get('/student/getStudentName', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentName'], [AuthMiddleware::class]);
$router->get('/student/verifyStudentGroup', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'verifyStudentGroup'], [AuthMiddleware::class]);
$router->get('/student/getSubjectNames', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getSubjectNames'], [AuthMiddleware::class]);
$router->get('/student/getStudentGrades', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentGrades'], [AuthMiddleware::class]);
// ⚠️ CHECK
$router->get('/student/getChildSubjectNames', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getChildSubjectNames'], [AuthMiddleware::class]);
$router->post('/student/addStudentGrade', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'addStudentGrade'], [AuthMiddleware::class]);
$router->get('/student/getGroupNames', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getGroupNames'], [AuthMiddleware::class]);
$router->post('/student/addStudentToGroup', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'addStudentToGroup'], [AuthMiddleware::class]);
$router->get('/student/verifyStudentUser', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'verifyStudentUser'], [AuthMiddleware::class]);
$router->post('/student/getStudentsNames', [Vendor\Schoolarsystem\Controllers\StudentsController::class, 'getStudentsNames'], [AuthMiddleware::class]);

// Subjects Routes ⚠️ CHECK API METHODS
$router->get('/subject/getSubjectById', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getSubjectById'], [AuthMiddleware::class]);
$router->get('/subject/getAllSubjects', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getAllSubjects'], [AuthMiddleware::class]);
$router->post('/subject/addSubject', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'addSubject'], [AuthMiddleware::class]);
$router->post('/subject/updateSubject', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'updateSubject'], [AuthMiddleware::class]);
$router->post('/subject/deleteSubjectById', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'deleteSubjectById'], [AuthMiddleware::class]);
$router->get('/subject/getChildSubjectFindById', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'getChildSubjectFindById'], [AuthMiddleware::class]);
$router->post('/subject/addSubjectChild', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'addSubjectChild'], [AuthMiddleware::class]);
$router->post('/subject/updateSubjectChild', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'updateSubjectChild'], [AuthMiddleware::class]);
$router->post('/subject/deleteSubjectChildById', [Vendor\Schoolarsystem\Controllers\SubjectsController::class, 'deleteSubjectChildById'], [AuthMiddleware::class]);

// Teachers Routes ✅ COMPLETE
$router->get('/teacher/getTeacherById', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'getTeacherById'], [AuthMiddleware::class]);
$router->get('/teacher/getAllTeachers', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'getAllTeachers'], [AuthMiddleware::class]);
$router->post('/teacher/addTeacher', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'addTeacher'], [AuthMiddleware::class]);
$router->post('/teacher/updateTeacher', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'updateTeacher'], [AuthMiddleware::class]);
$router->post('/teacher/deleteTeacherById', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'deleteTeacherById'], [AuthMiddleware::class]);
$router->get('/teacher/getAllTeachersUsers', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'getAllTeachersUsers'], [AuthMiddleware::class]);
$router->post('/teacher/addTeacherUser', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'addTeacherUser'], [AuthMiddleware::class]);
$router->post('/teacher/updateTeacherUserData', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'updateTeacherUserData'], [AuthMiddleware::class]);
$router->get('/teacher/verifyTeacherByUser', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'verifyTeacherByUser'], [AuthMiddleware::class]);
$router->post('/teacher/desactivateTeacherUser', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'desactivateTeacherUser'], [AuthMiddleware::class]);
$router->post('/teacher/reactivateTeacherUser', [Vendor\Schoolarsystem\Controllers\TeachersController::class, 'reactivateTeacherUser'], [AuthMiddleware::class]);
