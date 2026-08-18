var BASE_URL =
  window.location.protocol + "//" + window.location.host + "/public";

function initializeStudentDataTable() {
  let canManageStudents = false;

  $("#studentTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/student/getAllStudents`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        canManageStudents = data.permissions.canManageStudents;
        return data.data;
      },
    },
    columns: [
      { data: "name", className: "text-center py-2" },
      { data: "no_control", className: "text-center" },
      {
        data: "group_name",
        className: "text-center",
        defaultContent: "No asignado",
      },
      {
        data: "group_key",
        className: "text-center",
        defaultContent: "No asignado",
      },
      { data: "phone", className: "text-center" },
      {
        data: "academicalStatus",
        className: "text-center",
        render: function (data, type, row) {
          if (data === 1)
            return `<span class="badge text-bg-success" data-id="${row.studentId}" data-name="${row.name}" data-status="${data}">Activo</span>`;
          else if (data === 2)
            return `<span class="badge text-bg-warning" data-id="${row.studentId}" data-name="${row.name}" data-status="${data}">Baja Temporal</span>`;
          else if (data === 3)
            return `<span class="badge text-bg-danger" data-id="${row.studentId}" data-name="${row.name}" data-status="${data}">Inactivo/Baja</span>`;
          else if (data === 4)
            return `<span class="badge text-bg-primary" data-id="${row.studentId}" data-name="${row.name}" data-status="${data}">Egresado</span>`;
          else
            return `<span class="badge text-bg-secondary" data-id="${row.studentId}" data-name="${row.name}" data-status="${data}">${data}</span>`;
        },
      },
      {
        data: "permissions",
        render: function (data, type, row) {
          if (!canManageStudents) return ""; // si no hay permiso, celda vacía
          return `
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-circle dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-list"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item GradeStudent" href="#" data-encode="${row.encodeJWT}" data-student="${row.studentId}">
                                        <i class="bi bi-pencil-square"></i> Añadir Calificaciones
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item editStudent" href="#" data-id="${row.studentId}" data-bs-toggle="modal" data-bs-target="#StutentEditModal">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item deleteStudent" href="#" data-id="${row.studentId}">
                                        <i class="bi bi-trash-fill"></i> Eliminar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    `;
        },
        className: "text-center",
      },
    ],
  });
}

function initializeStudentPaymentDataTable() {
  let canManageStudents = false;

  $("#studentPaymentTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/student/getAllStudents`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        canManageStudents = data.permissions.canManageStudents;
        return data.data;
      },
    },
    columns: [
      { data: "name", className: "text-center py-2" },
      { data: "no_control", className: "text-center" },
      {
        data: "group_key",
        className: "text-center",
        defaultContent: "No asignado",
      },
      {
        data: "permissions",
        render: function (data, type, row) {
          if (!canManageStudents) return ""; // si no hay permiso, celda vacía
          return `<button data-id="${row.studentId}" data-name="${row.name}" class="btn btn-primary btn-circle viewPayments" data-bs-toggle="modal" data-bs-target="#studentPaymentsModal"><i class="bi bi-eye"></i></button>`;
        },
        className: "text-center",
      },
    ],
  });
}

function initializeStudentsUsersTable() {
  $("#studentsUsersTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/student/getAllStudentsUsers`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      //{ "data": "id", "className": "text-center" },
      { data: "name", className: "text-center" },
      { data: "user", className: "text-center", defaultContent: "No asignado" },
      {
        data: "status",
        className: "text-center",
        defaultContent: "Inactivo",
        render: function (data, type, row) {
          // Asigna el contenido por defecto "Inactivo" si data es null o vacío
          var statusText = data ? data : "Inactivo";
          var badgeClass =
            data === "Activo" ? "text-bg-success" : "text-bg-danger";
          // Si el contenido es "Inactivo", cambia la clase del badge
          if (statusText === "Inactivo") {
            badgeClass = "text-bg-secondary";
          }
          return (
            '<span class="badge ' +
            badgeClass +
            ' m-1">' +
            statusText +
            "</span>"
          );
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          if (row.status == "Activo") {
            return (
              '<button data-id="' +
              row.id +
              '" data-name="' +
              row.name +
              '" data-user="' +
              row.user +
              '" class="btn btn-primary btn-circle m-1 editStudentUser" data-bs-toggle="modal" data-bs-target="#StutentUserEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="' +
              row.id +
              '" class="btn btn-danger btn-circle m-1 desactivateStudentUser"><i class="bi bi-arrow-down-square-fill"></i></button>'
            );
          } else if (row.status == "Inactivo") {
            return (
              '<button data-id="' +
              row.id +
              '" class="btn btn-warning btn-circle reactivateStudentUser"><i class="bi bi-arrow-clockwise"></i></button>'
            );
          }
          return (
            '<button data-id="' +
            row.id +
            '" data-name="' +
            row.name +
            '" class="btn btn-primary btn-circle addUserStudents" data-bs-toggle="modal" data-bs-target="#StutentUserModal"><i class="bi bi-arrow-up-square-fill"></i></button>'
          );
        },
        className: "text-center",
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" data-name="' +
            row.name +
            '" class="btn btn-primary btn-circle toMicrosoft" data-bs-toggle="modal" data-bs-target="#toMicrosoftModal">Asociar cuenta Microsoft</button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeStudentsMicrosoftUsersTable() {
  $("#studentsMicrosoftUsersTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/student/getStudentsMicrosoftUsers`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "id", className: "text-center" },
      { data: "name", className: "text-center" },
      {
        data: "email",
        className: "text-center",
        defaultContent: "No asignado",
      },
      {
        data: null,
        render: function (data, type, row) {
          return '<span class="badge text-bg-success">Activo</span>';
        },
        className: "text-center",
      },
    ],
  });
}

function initializeStudentGrades(studentIdGroup) {
  $("#gradesStudentTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/student/getStudentGrades`,
      type: "GET",
      data: { studentId: studentIdGroup },
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "grade_id", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          if (row.subject_child_name == null) {
            return "<h6>" + row.subject_name + "</h6>";
          } else {
            return (
              "<h6>" +
              row.subject_name +
              "</h6><p>" +
              row.subject_child_name +
              "</p>"
            );
          }
        },
        className: "text-center",
      },
      { data: "continuous_grade", className: "text-center" },
      { data: "exam_grade", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          if (row.final_grade < 5.99 && row.final_grade > 0) {
            return (
              '<span class="badge text-bg-danger studentGrade" ' +
              'data-grade="' +
              row.grade_id +
              '" ' +
              'data-subject="' +
              row.subject_id +
              '" ' +
              'data-subjectname="' +
              row.subject_name +
              '" ' +
              'data-subjectchild="' +
              row.subject_child_id +
              '" ' +
              'data-subjectchildname="' +
              row.subject_child_name +
              '">' +
              row.final_grade +
              "</span>"
            );
          } else if (data.final_grade >= 6 && data.final_grade <= 7.99) {
            return (
              '<span class="badge text-bg-warning">' +
              data.final_grade +
              "</span>"
            );
          } else if (data.final_grade >= 8 && data.final_grade <= 10) {
            return (
              '<span class="badge text-bg-success">' +
              data.final_grade +
              "</span>"
            );
          } else {
            return '<span class="badge text-bg-secondary">No asignado</span>';
          }
        },
        className: "text-center",
      },
      {
        data: null,
        render: function (data, type, row) {
          if (row.makeOverId != null) {
            return (
              '<span class="badge text-bg-primary mekeOver" data-makeoverid="' +
              row.makeOverId +
              '" data-makeoverchildid="' +
              row.makeOverIdChild +
              '">Recursamiento</span>'
            );
          } else if (row.makeOverIdChild != null) {
            return (
              '<span class="badge text-bg-primary makeOverChild" data-makeoverid="' +
              row.makeOverId +
              '" data-makeoverchildid="' +
              row.makeOverIdChild +
              '">Recursamiento</span>'
            );
          } else {
            return '<span class="badge text-bg-secondary">-</span>';
          }
        },
        className: "text-center",
      },
      { data: "update_at", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" disabled class="btn btn-primary btn-circle editGrade" data-bs-toggle="modal" data-bs-target="#GradeEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="' +
            row.id +
            '" class="btn btn-danger btn-circle deleteGrade" disabled><i class="bi bi-trash-fill"></i></button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeTeachersDataTable() {
  $("#teachersTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/teacher/getAllTeachers`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "id", className: "text-center" },
      { data: "name", className: "text-center" },
      { data: "phone", className: "text-center" },
      { data: "email", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" data-name="' +
            row.name +
            '" class="btn btn-primary btn-circle m-1 editTeacher" data-bs-toggle="modal" data-bs-target="#TeacherEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="' +
            row.id +
            '" class="btn btn-danger btn-circle m-1 deleteTeacherById"><i class="bi bi-trash-fill"></i></button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeTeachersUsersTable() {
  $("#teacherUsersTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/teacher/getAllTeachersUsers`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "id", className: "text-center" },
      { data: "name", className: "text-center" },
      { data: "user", className: "text-center", defaultContent: "No asignado" },
      { data: "status", className: "text-center", defaultContent: "Inactivo" },
      {
        data: null,
        render: function (data, type, row) {
          if (row.status == "Activo") {
            return (
              '<button data-id="' +
              row.id +
              '" data-name="' +
              row.name +
              '" data-user="' +
              row.user +
              '" class="btn btn-primary btn-circle m-1 editTeacherUser" data-bs-toggle="modal" data-bs-target="#TeacherUserEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="' +
              row.id +
              '" class="btn btn-danger btn-circle m-1 desactivateTeacherUser"><i class="bi bi-arrow-down-square-fill"></i></button>'
            );
          } else if (row.status == "Inactivo") {
            return (
              '<button data-id="' +
              row.id +
              '" class="btn btn-warning btn-circle m-1 reactivateTeacherUser"><i class="bi bi-arrow-clockwise"></i></button>'
            );
          }
          return (
            '<button data-id="' +
            row.id +
            '" data-name="' +
            row.name +
            '" class="btn btn-primary btn-circle m-1 addUserTeachers" data-bs-toggle="modal" data-bs-target="#teacherUserModal"><i class="bi bi-arrow-up-square-fill"></i></button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeCarreersDataTable() {
  $("#carreersTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/carreer/getAllCarreers`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "id", className: "text-center" },
      { data: "nombre", className: "text-center" },
      { data: "area", className: "text-center" },
      { data: "subarea", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" class="btn btn-primary btn-circle m-1 subjectsCarreer" data-bs-toggle="modal" data-bs-target="#subjectsModal"><i class="bi bi-plus"></i></button>'
          );
        },
        className: "text-center",
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" class="btn btn-primary btn-circle m-1 editCarreer" data-bs-toggle="modal" data-bs-target="#CareerEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="' +
            row.id +
            '" class="btn btn-danger btn-circle m-1 deleteCarreerById"><i class="bi bi-trash-fill"></i></button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeGroupsDataTable() {
  let canManageGroups = false;

  $("#groupsTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/group/getAllGroups`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        canManageGroups = data.permissions.canManageGroups;
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "id", className: "text-center" },
      { data: "nombre_carrera", className: "text-center" },
      { data: "clave", className: "text-center" },
      { data: "name", className: "text-center" },
      {
        data: "members",
        render: function (data, type, row) {
          return `<span class="badge text-bg-light"><a href="#" class="groupDetails" data-id="${row.id}">${data} miembro(s)</a></span>`;
        },
        className: "text-center",
      },
      {
        data: "permissions",
        render: function (data, type, row) {
          if (!canManageGroups) return ""; // si no hay permiso, celda vacía
          return `
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-circle dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-list"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item groupSchedules" href="#" data-id="${row.id}">
                                        <i class="bi bi-calendar-date-fill"></i> Horarios
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item groupDetails" href="#" data-id="${row.id}">
                                        <i class="bi bi-eye-fill"></i> Detalles
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item editGroup" href="#" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#GroupsEditModal">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item deleteGroupById" href="#" data-id="${row.id}">
                                        <i class="bi bi-trash-fill"></i> Eliminar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    `;
        },
        className: "text-center",
      },
    ],
  });
}

function initializeGroupsStudentsDataTable(groupId) {
  $("#groupStudentsTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/group/getStudentsByGroupId`,
      type: "GET",
      data: { groupId: groupId },
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "student_id", className: "text-center" },
      { data: "student_name", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '</button><button data-id="' +
            row.student_id +
            '" data-group="' +
            row.group_id +
            '" class="btn btn-danger btn-circle deleteGroupStudent"><i class="bi bi-trash-fill"></i></button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeSubjectsDataTable() {
  $("#subjectsTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    paging: true,
    processing: true,
    ajax: {
      url: `${BASE_URL}/subject/getAllSubjects`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });

          return [];
        }
        return data.data;
      },
    },
    columns: [
      // Define las columnas
      { data: "id", className: "text-center" },
      {
        data: null,
        className: "text-center",
        render: function (data, type, row) {
          if (row.child === null) return `<h6>${row.name}</h6>`;
          else
            return `<h6>${row.name}</h6>
                            <p><a data-idFather="${row.id}" data-idChild="${row.id_child}" class="link-info link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover subjectChildInfo" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#childSubjectsModal">${row.child}</a></p>`;
        },
      },
      {
        data: "career",
        className: "text-center",
        render: function (data) {
          if (data === null || data === "" || data === undefined) {
            return "Aún no asignada";
          }
          return data;
        },
      },
      {
        data: "description",
        className: "text-center",
        render: function (data) {
          if (data === null || data === "" || data === undefined) {
            return "Sin descripción";
          }
          return data;
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" data-name="' +
            row.name +
            '" data-carrerid="' +
            row.id_carrer +
            '" class="btn btn-primary btn-circle m-1 addChildSubject" data-bs-toggle="modal" data-bs-target="#SubjectsChildAddModal"><i class="bi bi-capslock-fill"></i></button><button data-id="'
            // row.id +
            // '" class="btn btn-warning btn-circle editChildSubject"><i class="bi bi-pencil-fill"></i></button>'
          );
        },
        className: "text-center",
      },
      {
        data: null,
        render: function (data, type, row) {
          return (
            '<button data-id="' +
            row.id +
            '" class="btn btn-primary btn-circle m-1 editSubject" data-bs-toggle="modal" data-bs-target="#SubjectsEditModal"><i class="bi bi-pencil-square"></i></button><button data-id="' +
            row.id +
            '" class="btn btn-danger btn-circle m-1 deleteSubjectById"><i class="bi bi-trash-fill"></i></button>'
          );
        },
        className: "text-center",
      },
    ],
  });
}

function initializeDuplicatesDataTable() {
  $("#duplicatesTable").DataTable({
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
    },
    ordering: false,
    processing: true,
    ajax: {
      url: `${BASE_URL}/group/getDuplicateStudents`,
      type: "GET",
      dataSrc: function (data) {
        if (!data.success) {
          Swal.fire({ icon: "error", title: "Error", text: data.message });
          return [];
        }
        return data.data;
      },
    },
    columns: [
      { data: "nombre", className: "text-center" },
      {
        data: "total_grupos",
        className: "text-center",
        render: function (data) {
          return `<span class="badge bg-danger">${data} grupos</span>`;
        },
      },
      {
        data: null,
        className: "text-center",
        render: function (data, type, row) {
          return `
                        <button class="btn btn-sm btn-warning btnResolveDuplicate"
                            data-student-id="${row.id}"
                            data-student-name="${row.nombre}"
                            data-bs-toggle="modal"
                            data-bs-target="#duplicatesModal">
                            <i class="bi bi-wrench"></i> Resolver
                        </button>
                    `;
        },
      },
    ],
  });
}

export {
  initializeStudentDataTable,
  initializeStudentPaymentDataTable,
  initializeStudentsUsersTable,
  initializeStudentsMicrosoftUsersTable,
  initializeTeachersDataTable,
  initializeTeachersUsersTable,
  initializeCarreersDataTable,
  initializeGroupsDataTable,
  initializeGroupsStudentsDataTable,
  initializeSubjectsDataTable,
  initializeStudentGrades,
  initializeDuplicatesDataTable,
};
