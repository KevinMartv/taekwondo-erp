<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - Taekwondo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hidden { display: none !important; }
        /* Scroll personalizado para la tabla */
        .custom-scrollbar::-webkit-scrollbar { height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-6 font-sans text-gray-800">

    <div class="max-w-[1400px] mx-auto">
        <!-- Header -->
        <header class="mb-8 border-b pb-4 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestión de Alumnos</h1>
                <p class="text-gray-500">Padrón de estudiantes y altas</p>
            </div>
        </header>

        <!-- Layout principal: Formulario a la izquierda, Tabla a la derecha -->
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- PANEL IZQUIERDO: FORMULARIO DE ALTA (1/3) -->
            <div class="w-full lg:w-1/3 bg-white p-6 rounded-lg shadow-sm border border-gray-200 h-fit">
                <h2 class="text-xl font-semibold mb-4 text-blue-800 border-b pb-2">Registrar Nuevo Alumno</h2>
                
                <!-- Alertas -->
                <div id="form_errors" class="hidden mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                    <p class="font-bold mb-1">Error al guardar:</p>
                    <ul id="form_errors_list" class="list-disc list-inside"></ul>
                </div>
                <div id="form_success" class="hidden mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm font-semibold">
                    Alumno registrado exitosamente.
                </div>

                <form id="form_alumno" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre(s) *</label>
                        <input type="text" id="nombre" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ap. Paterno *</label>
                            <input type="text" id="apellido_paterno" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ap. Materno</label>
                            <input type="text" id="apellido_materno" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nacimiento *</label>
                            <input type="date" id="fecha_nacimiento" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                            <input type="tel" id="telefono_contacto" required pattern="[0-9]{10}" placeholder="10 dígitos" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nivel (Cinta) *</label>
                        <select id="nivel_id" required class="w-full border border-gray-300 rounded-md p-2 bg-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Cargando niveles...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horarios Asignados *</label>
                        <div id="horarios_container" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-gray-50 space-y-2 text-sm">
                            <!-- Los checkboxes se inyectan por JS -->
                            <span class="text-gray-500">Cargando horarios...</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Ingreso *</label>
                        <input type="date" id="fecha_ingreso" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 mt-2">
                        Registrar Alumno
                    </button>
                </form>
            </div>

            <!-- PANEL DERECHO: PADRÓN DE ALUMNOS (2/3) -->
            <div class="w-full lg:w-2/3 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="text-xl font-semibold text-gray-800">Padrón de Alumnos</h2>
                    <button onclick="loadAlumnos()" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 py-1 px-3 rounded border">
                        ↻ Refrescar
                    </button>
                </div>
                
                <div class="overflow-x-auto custom-scrollbar pb-2">
                    <table class="w-full text-sm text-left border-collapse min-w-[800px]">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="p-3 border-b">ID</th>
                                <th class="p-3 border-b">Alumno</th>
                                <th class="p-3 border-b">Nivel</th>
                                <th class="p-3 border-b">Contacto</th>
                                <th class="p-3 border-b">Horarios</th>
                                <th class="p-3 border-b text-center">Estado</th>
                                <th class="p-3 border-b text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_alumnos">
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">Cargando alumnos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        // CONFIGURACIÓN DE LA API (Asegúrate que coincida con tu backend)
        const API_URL = 'http://localhost:8001/api';

        // Al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('fecha_ingreso').valueAsDate = new Date();
            loadCatalogos();
            loadAlumnos();
        });

        // --- 1. CARGA DE CATÁLOGOS (Niveles y Horarios) ---
        async function loadCatalogos() {
            try {
                // Fetch Niveles
                const resNiveles = await fetch(`${API_URL}/niveles`);
                if(resNiveles.ok) {
                    const niveles = await resNiveles.json();
                    const selectNivel = document.getElementById('nivel_id');
                    selectNivel.innerHTML = '<option value="">Seleccione un nivel...</option>';
                    niveles.forEach(nivel => {
                        selectNivel.innerHTML += `<option value="${nivel.id}">${nivel.nombre}</option>`;
                    });
                }

                // Fetch Horarios
                const resHorarios = await fetch(`${API_URL}/horarios`);
                if(resHorarios.ok) {
                    const horarios = await resHorarios.json();
                    const containerHorarios = document.getElementById('horarios_container');
                    containerHorarios.innerHTML = ''; // Limpiar
                    horarios.forEach(h => {
                        // Creamos checkboxes para permitir selección múltiple
                        const div = document.createElement('div');
                        div.className = "flex items-center";
                        div.innerHTML = `
                            <input type="checkbox" id="horario_${h.id}" name="horarios[]" value="${h.id}" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                            <label for="horario_${h.id}" class="text-gray-700 capitalize">${h.dia_semana}: ${h.hora_inicio.slice(0,5)} - ${h.hora_fin.slice(0,5)}</label>
                        `;
                        containerHorarios.appendChild(div);
                    });
                }
            } catch (error) {
                console.error("Error cargando catálogos. ¿Están creados los endpoints /niveles y /horarios en Laravel?", error);
            }
        }

        // --- 2. CARGA DEL PADRÓN DE ALUMNOS ---
        async function loadAlumnos() {
            const tbody = document.getElementById('tabla_alumnos');
            try {
                const response = await fetch(`${API_URL}/alumnos`);
                const alumnos = await response.json();
                
                tbody.innerHTML = '';
                
                if(alumnos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="p-6 text-center text-gray-500">No hay alumnos registrados.</td></tr>';
                    return;
                }

                alumnos.forEach(a => {
                    const nombreCompleto = `${a.nombre} ${a.apellido_paterno} ${a.apellido_materno || ''}`.trim();
                    const nivel = a.nivel ? a.nivel.nombre : 'Sin Nivel';
                    
                    // Formatear horarios en una lista pequeña
                    let horariosHtml = '<span class="text-gray-400 text-xs">Sin horario asignado</span>';
                    if(a.horarios && a.horarios.length > 0) {
                        horariosHtml = `<ul class="list-disc list-inside text-xs text-gray-600">` + 
                            a.horarios.map(h => `<li class="capitalize">${h.dia_semana} (${h.hora_inicio.slice(0,5)})</li>`).join('') 
                            + `</ul>`;
                    }

                    // Badge de estado
                    const statusBadge = a.activo === 1 || a.activo === true || a.activo === '1' 
                        ? `<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-200">Activo</span>` 
                        : `<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded border border-red-200">Baja</span>`;

                    // Botón de acción dinámico
                    const actionBtn = a.activo === 1 || a.activo === true || a.activo === '1'
                        ? `<button onclick="toggleEstado(${a.id}, 'desactivar')" class="text-xs bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 py-1 px-2 rounded w-full">Dar de Baja</button>`
                        : `<button onclick="toggleEstado(${a.id}, 'activar')" class="text-xs bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 py-1 px-2 rounded w-full">Reactivar</button>`;

                    const tr = document.createElement('tr');
                    tr.className = "border-b hover:bg-gray-50 bg-white";
                    tr.innerHTML = `
                        <td class="p-3 font-semibold text-gray-500">#${a.id}</td>
                        <td class="p-3 font-medium text-gray-900">${nombreCompleto}</td>
                        <td class="p-3">${nivel}</td>
                        <td class="p-3">${a.telefono_contacto}</td>
                        <td class="p-3">${horariosHtml}</td>
                        <td class="p-3 text-center">${statusBadge}</td>
                        <td class="p-3 text-center">${actionBtn}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-red-500">Error al cargar alumnos: ${error.message}</td></tr>`;
            }
        }

        // --- 3. REGISTRAR ALUMNO (POST) ---
        document.getElementById('form_alumno').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            document.getElementById('form_errors').classList.add('hidden');
            document.getElementById('form_success').classList.add('hidden');
            const errorsList = document.getElementById('form_errors_list');
            errorsList.innerHTML = '';

            // Recolectar IDs de horarios (Checkboxes seleccionados)
            const horariosSeleccionados = Array.from(document.querySelectorAll('input[name="horarios[]"]:checked'))
                                              .map(cb => parseInt(cb.value));

            if(horariosSeleccionados.length === 0) {
                document.getElementById('form_errors').classList.remove('hidden');
                errorsList.innerHTML = `<li>Debe seleccionar al menos un horario.</li>`;
                return;
            }

            const payload = {
                nombre: document.getElementById('nombre').value,
                apellido_paterno: document.getElementById('apellido_paterno').value,
                apellido_materno: document.getElementById('apellido_materno').value,
                fecha_nacimiento: document.getElementById('fecha_nacimiento').value,
                telefono_contacto: document.getElementById('telefono_contacto').value,
                nivel_id: parseInt(document.getElementById('nivel_id').value),
                fecha_ingreso: document.getElementById('fecha_ingreso').value,
                horarios: horariosSeleccionados
            };

            try {
                const response = await fetch(`${API_URL}/alumnos`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.status === 422) {
                    document.getElementById('form_errors').classList.remove('hidden');
                    if (data.errors) {
                        for (const campo in data.errors) {
                            data.errors[campo].forEach(msg => {
                                const li = document.createElement('li');
                                li.textContent = msg;
                                errorsList.appendChild(li);
                            });
                        }
                    } else if (data.message) {
                        const li = document.createElement('li');
                        li.textContent = data.message;
                        errorsList.appendChild(li);
                    }
                } else if (response.status === 201 || response.ok) {
                    document.getElementById('form_success').classList.remove('hidden');
                    document.getElementById('form_alumno').reset();
                    document.getElementById('fecha_ingreso').valueAsDate = new Date();
                    loadAlumnos(); // Refrescar la tabla
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                document.getElementById('form_errors').classList.remove('hidden');
                errorsList.innerHTML = `<li>Error de red: ${error.message}</li>`;
            }
        });

        // --- 4. ALTERAR ESTADO DEL ALUMNO (Baja Lógica / PATCH) ---
        async function toggleEstado(alumnoId, accion) {
            const confirmMessage = accion === 'desactivar' 
                ? '¿Estás seguro de dar de baja a este alumno?' 
                : '¿Reactivar el registro de este alumno?';

            if(!confirm(confirmMessage)) return;

            try {
                const response = await fetch(`${API_URL}/alumnos/${alumnoId}/toggle-estado`, {
                    method: 'PATCH', // Cambia a 'PUT' si tu backend lo exige así
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    // Refrescar tabla silenciosamente
                    loadAlumnos();
                } else {
                    const data = await response.json();
                    alert(`Error al cambiar estado: ${data.message || 'Revisa tu backend'}`);
                }
            } catch (error) {
                alert('Error de conexión con el servidor.');
            }
        }
    </script>
</body>
</html>