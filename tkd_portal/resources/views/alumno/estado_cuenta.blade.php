<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Estado de Cuenta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200 text-center">
                
                <h3 id="mensaje_bienvenida" class="text-2xl font-bold text-gray-800 mb-2">Cargando tu información...</h3>
                <p class="text-gray-500 mb-8">Aquí puedes revisar tu estatus actual con la escuela.</p>

                <div id="status_card" class="max-w-md mx-auto p-6 rounded-xl border mb-8 hidden">
                    <p class="text-sm uppercase tracking-wider mb-2 font-semibold">Estatus de Pagos</p>
                    <p id="lbl_estado" class="text-3xl font-extrabold"></p>
                    <p id="lbl_vencimiento" class="text-sm mt-4"></p>
                </div>

                <!-- Botón de conexión al POS -->
                <div class="mt-10 pt-6 border-t border-gray-100">
                    <p class="text-gray-600 mb-4">¿Necesitas comprar equipo o renovar tu suscripción?</p>
                    <a href="{{ route('alumno.tienda') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                        Ir a la Tienda (POS)
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Inyectamos el ID del alumno logueado desde Laravel
        const ALUMNO_ID = {{ auth()->user()->alumno_id ?? 'null' }};
        const PAGOS_API = 'http://localhost:8002/api';

        async function cargarMiEstado() {
            if (!ALUMNO_ID) {
                document.getElementById('mensaje_bienvenida').innerText = "Tu cuenta no está vinculada a un expediente de alumno.";
                return;
            }

            try {
                const res = await fetch(`${PAGOS_API}/alumnos/${ALUMNO_ID}/estado-cuenta`);
                const data = await res.json();
                
                const statusCard = document.getElementById('status_card');
                const lblEstado = document.getElementById('lbl_estado');
                const lblVencimiento = document.getElementById('lbl_vencimiento');
                
                statusCard.classList.remove('hidden');
                document.getElementById('mensaje_bienvenida').innerText = "Resumen de tu cuenta";

                if(data.estado_cuenta === 'al_dia') {
                    lblEstado.innerText = "¡ESTÁS AL DÍA!";
                    statusCard.className = "max-w-md mx-auto p-6 rounded-xl border mb-8 bg-green-50 border-green-200 text-green-700";
                    lblVencimiento.innerText = `Próximo pago: ${data.proximo_vencimiento}`;
                } else {
                    lblEstado.innerText = "TIENES UN ADEUDO";
                    statusCard.className = "max-w-md mx-auto p-6 rounded-xl border mb-8 bg-red-50 border-red-200 text-red-700";
                    lblVencimiento.innerText = "Por favor, regulariza tu situación lo antes posible.";
                }
            } catch (error) {
                document.getElementById('mensaje_bienvenida').innerText = "Error al cargar el estado de cuenta.";
            }
        }
        
        cargarMiEstado();
    </script>
</x-app-layout>