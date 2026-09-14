<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Control de Pagos - Panel Administrativo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- PANEL IZQUIERDO: FORMULARIO DE REGISTRO DE PAGO -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4 text-blue-800 border-b pb-2">Registrar Nuevo Pago</h2>
                    
                    <div id="form_errors" class="hidden mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                        <p class="font-bold mb-1">Error al guardar el pago:</p>
                        <ul id="form_errors_list" class="list-disc list-inside"></ul>
                    </div>
                    
                    <div id="form_success" class="hidden mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm font-semibold">
                        Pago registrado exitosamente.
                    </div>

                    <form id="form_pago" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID del Alumno *</label>
                                <input type="number" id="alumno_id" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto ($) *</label>
                                <input type="number" step="0.01" id="monto" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago *</label>
                                <select id="metodo_pago" required class="w-full border border-gray-300 rounded-md p-2 bg-white focus:ring-blue-500 focus:border-blue-500">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                            <div id="rastreo_container" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número de Rastreo *</label>
                                <input type="text" id="numero_rastreo" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej. ABC123">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo de Pago *</label>
                                <select id="ciclo_pago" required class="w-full border border-gray-300 rounded-md p-2 bg-white focus:ring-blue-500 focus:border-blue-500">
                                    <option value="mes">Mensual</option>
                                    <option value="quincena">Quincenal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Periodo Cubierto *</label>
                                <input type="date" id="periodo_cubierto" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Pago *</label>
                            <input type="date" id="fecha_pago" required class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Asegúrate de pegar este botón aquí -->
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 mt-4">
                            Registrar Pago
                        </button>
                    </form>
                </div>

                <!-- PANEL DERECHO: CONSULTA DE ESTADO DE CUENTA -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Consultar Estado de Cuenta</h2>
                    
                    <form id="form_consulta" class="flex gap-2 mb-6">
                        <input type="number" id="consulta_alumno_id" placeholder="ID del Alumno" required class="flex-1 border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                            Buscar
                        </button>
                    </form>

                    <div id="consulta_error" class="hidden mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-md border border-red-200"></div>

                    <div id="estado_cuenta_result" class="hidden">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-4 rounded-md border text-center" id="status_card">
                                <p class="text-sm text-gray-500 mb-1">Estatus Actual</p>
                                <p class="text-2xl font-bold uppercase tracking-wide" id="lbl_estado"></p>
                            </div>
                            <div class="p-4 rounded-md border bg-gray-50 flex flex-col justify-center">
                                <p class="text-sm text-gray-600"><strong>Último periodo:</strong> <span id="lbl_ultimo"></span></p>
                                <p class="text-sm text-gray-600 mt-1"><strong>Próximo vto:</strong> <span id="lbl_vencimiento"></span></p>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-2 text-gray-700">Historial de Pagos</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse">
                                <thead class="bg-gray-100 text-gray-600">
                                    <tr>
                                        <th class="p-2 border">Fecha</th>
                                        <th class="p-2 border">Monto</th>
                                        <th class="p-2 border">Método</th>
                                        <th class="p-2 border">Ciclo</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_historial"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const API_URL = 'http://localhost:8002/api';

        document.getElementById('fecha_pago').valueAsDate = new Date();

        const metodoPagoSelect = document.getElementById('metodo_pago');
        const rastreoContainer = document.getElementById('rastreo_container');
        const rastreoInput = document.getElementById('numero_rastreo');

        metodoPagoSelect.addEventListener('change', (e) => {
            if (e.target.value === 'transferencia') {
                rastreoContainer.classList.remove('hidden');
                rastreoInput.required = true;
            } else {
                rastreoContainer.classList.add('hidden');
                rastreoInput.required = false;
                rastreoInput.value = '';
            }
        });

        document.getElementById('form_pago').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            document.getElementById('form_errors').classList.add('hidden');
            document.getElementById('form_success').classList.add('hidden');
            const errorsList = document.getElementById('form_errors_list');
            errorsList.innerHTML = '';

            const payload = {
                alumno_id: parseInt(document.getElementById('alumno_id').value),
                monto: parseFloat(document.getElementById('monto').value),
                metodo_pago: document.getElementById('metodo_pago').value,
                ciclo_pago: document.getElementById('ciclo_pago').value,
                periodo_cubierto: document.getElementById('periodo_cubierto').value,
                fecha_pago: document.getElementById('fecha_pago').value
            };

            if (payload.metodo_pago === 'transferencia') {
                payload.numero_rastreo = document.getElementById('numero_rastreo').value;
            }

            try {
                const response = await fetch(`${API_URL}/pagos`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
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
                    document.getElementById('form_pago').reset();
                    document.getElementById('fecha_pago').valueAsDate = new Date();
                    metodoPagoSelect.dispatchEvent(new Event('change'));

                    const consultaId = document.getElementById('consulta_alumno_id').value;
                    if(consultaId && parseInt(consultaId) === payload.alumno_id) {
                        document.getElementById('form_consulta').dispatchEvent(new Event('submit'));
                    }
                } else {
                    throw new Error('Error inesperado del servidor');
                }
            } catch (error) {
                document.getElementById('form_errors').classList.remove('hidden');
                errorsList.innerHTML = `<li>Error de conexión: ${error.message}</li>`;
            }
        });

        document.getElementById('form_consulta').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const alumnoId = document.getElementById('consulta_alumno_id').value;
            const errorContainer = document.getElementById('consulta_error');
            const resultContainer = document.getElementById('estado_cuenta_result');
            
            errorContainer.classList.add('hidden');
            resultContainer.classList.add('hidden');

            try {
                const response = await fetch(`${API_URL}/alumnos/${alumnoId}/estado-cuenta`, {
                    headers: { 'Accept': 'application/json' }
                });
                
                const data = await response.json();

                if (response.ok) {
                    const statusCard = document.getElementById('status_card');
                    const lblEstado = document.getElementById('lbl_estado');
                    
                    if (data.estado_cuenta === 'al_dia') {
                        lblEstado.textContent = 'Al Día';
                        statusCard.className = 'p-4 rounded-md border text-center bg-green-50 border-green-200 text-green-700';
                    } else {
                        lblEstado.textContent = 'Adeudo';
                        statusCard.className = 'p-4 rounded-md border text-center bg-red-50 border-red-200 text-red-700';
                    }

                    document.getElementById('lbl_ultimo').textContent = data.ultimo_periodo_pagado || 'N/A';
                    document.getElementById('lbl_vencimiento').textContent = data.proximo_vencimiento || 'N/A';

                    const tbody = document.getElementById('tabla_historial');
                    tbody.innerHTML = '';

                    if (data.historial && data.historial.length > 0) {
                        data.historial.forEach(pago => {
                            const tr = document.createElement('tr');
                            tr.className = "border-b hover:bg-gray-50";
                            tr.innerHTML = `
                                <td class="p-2 border">${pago.fecha_pago || pago.created_at || '-'}</td>
                                <td class="p-2 border font-medium">$${pago.monto}</td>
                                <td class="p-2 border capitalize">${pago.metodo_pago} ${pago.numero_rastreo ? `<br><span class="text-xs text-gray-500">Ref: ${pago.numero_rastreo}</span>` : ''}</td>
                                <td class="p-2 border capitalize">${pago.ciclo_pago}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">No hay pagos registrados</td></tr>';
                    }

                    resultContainer.classList.remove('hidden');
                } else {
                    errorContainer.textContent = data.message || 'No se pudo cargar el estado de cuenta. Verifica que el ID exista.';
                    errorContainer.classList.remove('hidden');
                }
            } catch (error) {
                errorContainer.textContent = 'Error de red: No se pudo conectar con el servidor.';
                errorContainer.classList.remove('hidden');
            }
        });
    </script>
</x-app-layout>