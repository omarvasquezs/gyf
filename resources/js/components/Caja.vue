<template>
    <div class="container">
        <!-- Back Button -->
        <div class="mb-4">
            <button @click="goBack" class="btn btn-link">← Regresar</button>
        </div>

        <!-- Selection Section -->
        <div class="card mb-4">
            <div class="card-header">Servicio</div>
            <div class="card-body">
                <div class="form-group position-relative select-wrapper">
                    <label for="tipo-servicio-select">Tipo de Servicio</label>
                    <select v-model="comprobanteType" class="form-control pointer" id="tipo-servicio-select">
                        <option value="" disabled selected class="pointer">Seleccione tipo de servicio</option>
                        <option value="citas" class="pointer">Citas</option>
                        <option value="productos" class="pointer">Productos</option>
                    </select>
                    <i class="fas fa-chevron-down select-arrow"></i>
                </div>
            </div>
        </div>

        <!-- Citas Section -->
        <div v-if="comprobanteType === 'citas'">
            <!-- Search Patient Section -->
            <div class="card mb-4">
                <div class="card-header">Búsqueda de Paciente</div>
                <div class="card-body">
                    <div class="form-group position-relative select-wrapper">
                        <label for="paciente-select">Seleccionar Paciente</label>
                        <select 
                            v-model="selectedPatientId" 
                            class="form-control pointer" 
                            id="paciente-select"
                            @change="fetchPatientAppointments"
                        >
                            <option value="" disabled selected class="pointer">Seleccione un paciente</option>
                            <option v-for="paciente in pacientes" :key="paciente.id" :value="paciente.id" class="pointer">
                                {{ paciente.num_historia }} - {{ paciente.nombre }}
                            </option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>

            <!-- Patient Pending Appointments Section -->
            <div v-if="selectedPatient" class="card mb-4">
                <div class="card-header">
                    Citas Pendientes - {{ selectedPatient.nombre }}
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    Seleccionar
                                </th>
                                <th>Fecha</th>
                                <th>Tipo de Cita</th>
                                <th>Médico</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cita in pendingAppointments" :key="cita.id" @click="selectAppointment(cita.id)" class="clickable-row">
                                <td>
                                    <input 
                                        type="radio" 
                                        v-model="selectedAppointmentId" 
                                        :value="cita.id"
                                    >
                                </td>
                                <td>{{ formatDate(cita.fecha) }}</td>
                                <td>{{ cita.tipo_cita }}</td>
                                <td>{{ cita.medico }}</td>
                                <td>{{ formatCurrency(cita.monto) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Productos Section -->
        <div v-if="comprobanteType === 'productos'">
            <!-- List Producto Comprobante Section -->
            <div class="card mb-4">
                <div class="card-header">Lista de solicitudes de compra</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center">Seleccionar</th>
                                <th>Nombres</th>
                                <th>DNI/CE</th>
                                <th>Teléfono</th>
                                <th>Monto Total</th>
                                <th>Registrado en</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="productoComprobante in productoComprobantes" :key="productoComprobante.id" class="clickable-row">
                                <td class="text-center" @click="selectProductoComprobante(productoComprobante.id)">
                                    <input 
                                        type="radio" 
                                        v-model="selectedProductoComprobanteId" 
                                        :value="productoComprobante.id"
                                    >
                                </td>
                                <td @click="selectProductoComprobante(productoComprobante.id)">{{ productoComprobante.nombres }}</td>
                                <td @click="selectProductoComprobante(productoComprobante.id)">{{ productoComprobante.dni_ce ? productoComprobante.dni_ce : 'N/A' }}</td>
                                <td @click="selectProductoComprobante(productoComprobante.id)">{{ productoComprobante.telefono }}</td>
                                <td @click="selectProductoComprobante(productoComprobante.id)">{{ formatCurrency(productoComprobante.monto_total) }}</td>
                                <td @click="selectProductoComprobante(productoComprobante.id)">{{ formatDate(productoComprobante.created_at) }}</td>
                                <td class="text-center">
                                    <button 
                                        @click.stop="deleteProductoComprobante(productoComprobante.id)"
                                        class="btn btn-sm btn-danger"
                                        title="Eliminar solicitud"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Producto Comprobante Items Section -->
            <div v-if="selectedProductoComprobante" class="card mb-4">
                <div class="card-header">
                    Detallado de productos de la solicitud seleccionada
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo de Producto</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Precio Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in productoComprobanteItems" :key="item.id">
                                <td>{{ item.stock ? getTipoProductoLabel(item.stock.tipo_producto) : 'N/A' }}</td>
                                <td>{{ item.stock ? item.stock.descripcion : 'N/A' }}</td>
                                <td class="text-center">
                                  <span v-if="item.stock && item.stock.tipo_producto === 'u'">N/A</span>
                                  <span v-else>{{ item.cantidad }}</span>
                                </td>
                                <td>{{ formatCurrency(item.precio_unitario) }}</td>
                                <td>{{ formatCurrency(item.precio_total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>{{ formatCurrency(totalPrecio) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div v-if="(comprobanteType === 'citas' && selectedAppointmentId) || (comprobanteType === 'productos' && selectedProductoComprobante)" class="card mb-4">
            <div class="card-header">Generar Comprobante</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group position-relative select-wrapper">
                            <label>Tipo de Comprobante</label>
                            <select v-model="comprobante.tipo" class="form-control pointer" @change="onTipoComprobanteChange">
                                <option value="b" class="pointer">Boleta</option>
                                <option value="f" class="pointer">Factura</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group position-relative select-wrapper">
                            <label>Método de Pago <span class="text-danger">*</span></label>
                            <select v-model="comprobante.id_metodo_pago" class="form-control pointer" required>
                                <option value="" disabled selected class="pointer">Seleccione método de pago</option>
                                <option v-for="metodo in metodosPago" 
                                        :key="metodo.id" 
                                        :value="metodo.id"
                                        class="pointer">
                                    {{ metodo.nombre }}
                                </option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                </div>

                <!-- RUC and Razón Social fields - only show when Factura is selected -->
                <div v-if="comprobante.tipo === 'f'" class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numero_ruc">N° de RUC <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                v-model="comprobante.numero_ruc" 
                                class="form-control" 
                                id="numero_ruc" 
                                placeholder="Ingrese número de RUC"
                                pattern="[0-9]{11}"
                                maxlength="11"
                                @input="validateRuc"
                                required
                            >
                            <div v-if="rucError" class="text-danger small mt-1">{{ rucError }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="razon_social">Razón Social <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                v-model="comprobante.razon_social" 
                                class="form-control" 
                                id="razon_social" 
                                placeholder="Ingrese razón social"
                                required
                            >
                        </div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button 
                        class="btn btn-success" 
                        @click="generateComprobante"
                    >
                        Generar Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            comprobanteType: '', // Type of service selected (citas or productos)
            searchTerm: '', // Search term for filtering patients
            pacientes: [], // List of patients
            selectedPatientId: '', // ID of the selected patient
            selectedPatient: null, // Details of the selected patient
            pendingAppointments: [], // List of pending appointments for the selected patient
            selectedAppointmentId: '', // ID of the selected appointment
            metodosPago: [], // List of payment methods
            comprobante: {
                tipo: 'b', // Type of comprobante (boleta or factura)
                numero_ruc: '',
                razon_social: '',
                id_metodo_pago: '' // Selected payment method ID
            },
            productoComprobantes: [], // List of product purchase requests
            selectedProductoComprobanteId: '', // ID of the selected product purchase request
            selectedProductoComprobante: null, // Details of the selected product purchase request
            productoComprobanteItems: [], // List of items in the selected product purchase request
            rucError: '' // RUC validation error message
        }
    },
    computed: {
        // Calculate the total price of the selected product purchase request items
        totalPrecio() {
            return this.productoComprobanteItems.reduce((total, item) => total + item.precio_total, 0);
        }
    },
    mounted() {
        // Fetch initial data when the component is mounted
        this.fetchPacientes();
        this.fetchMetodosPago();
        this.fetchProductoComprobantes();
    },
    methods: {
        // Navigate back to the previous page
        goBack() {
            window.history.back();
        },
        // Fetch the list of patients from the API
        async fetchPacientes() {
            try {
                // Updated to use the new endpoint that returns only patients with citas
                const response = await axios.get('/api/pacientes-with-citas');
                this.pacientes = response.data;
            } catch (error) {
                console.error('Error fetching pacientes:', error);
                this.pacientes = [];
            }
        },
        // Fetch the appointments of the selected patient from the API
        async fetchPatientAppointments() {
            try {
                const selectedPatient = this.pacientes.find(p => p.id === this.selectedPatientId);
                if (!selectedPatient) {
                    console.error('No patient found with selected ID');
                    return;
                }
                
                const response = await axios.get(`/api/pacientes/search/${selectedPatient.num_historia}`);
                this.selectedPatient = response.data.patient;
                this.pendingAppointments = response.data.pendingAppointments;
            } catch (error) {
                console.error('Error fetching patient appointments:', error);
            }
        },
        // Fetch the list of payment methods from the API
        async fetchMetodosPago() {
            try {
                const response = await axios.get('/api/active-metodos-pago');
                this.metodosPago = Array.isArray(response.data) ? response.data : [];
            } catch (error) {
                console.error('Error fetching metodos de pago:', error);
            }
        },
        // Fetch the list of product purchase requests from the API
        async fetchProductoComprobantes() {
            try {
                const response = await axios.get('/api/productos-comprobante');
                const data = Array.isArray(response.data.data) ? response.data.data : 
                            Array.isArray(response.data) ? response.data : [];
                this.productoComprobantes = data;
            } catch (error) {
                console.error('Error fetching producto comprobantes:', error);
                this.productoComprobantes = [];
            }
        },
        // Fetch the items of the selected product purchase request from the API
        async fetchProductoComprobanteItems() {
            try {
                const response = await axios.get(`/api/productos-comprobante/${this.selectedProductoComprobanteId}`);
                this.selectedProductoComprobante = response.data;
                this.productoComprobanteItems = response.data.items.map(item => ({
                    ...item,
                    precio_unitario: item.precio,
                    precio_total: item.precio * item.cantidad
                }));
            } catch (error) {
                console.error('Error fetching producto comprobante items:', error);
            }
        },
        // Format a date to a readable string
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleString('es-PE', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        // Format a number to a currency string
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-PE', {
                style: 'currency',
                currency: 'PEN'
            }).format(amount);
        },
        // Select a specific appointment
        selectAppointment(id) {
            this.selectedAppointmentId = id;
        },
        // Select a product purchase request and fetch its items
        selectProductoComprobante(id) {
            this.selectedProductoComprobanteId = id;
            this.fetchProductoComprobanteItems();
        },
        // Generate a comprobante based on the selected service type and details
        async generateComprobante() {
            if (this.comprobanteType === 'citas' && !this.selectedPatient) {
                alert('Seleccione un paciente antes de generar el comprobante.');
                return;
            }
            
            if (!this.comprobante.id_metodo_pago) {
                alert('Por favor seleccione un método de pago.');
                return;
            }

            // Validate RUC and Razón Social for Factura
            if (this.comprobante.tipo === 'f') {
                if (!this.comprobante.numero_ruc || this.comprobante.numero_ruc.trim() === '') {
                    alert('El número de RUC es obligatorio para facturas.');
                    return;
                }
                if (!this.comprobante.razon_social || this.comprobante.razon_social.trim() === '') {
                    alert('La razón social es obligatoria para facturas.');
                    return;
                }
                if (!/^[0-9]{11}$/.test(this.comprobante.numero_ruc)) {
                    alert('El número de RUC debe tener exactamente 11 dígitos.');
                    return;
                }
            }

            try {
                let response;
                const comprobanteData = {
                    tipo: this.comprobante.tipo,
                    id_metodo_pago: this.comprobante.id_metodo_pago
                };

                // Add RUC and Razón Social if it's a factura
                if (this.comprobante.tipo === 'f') {
                    comprobanteData.numero_ruc = this.comprobante.numero_ruc;
                    comprobanteData.razon_social = this.comprobante.razon_social;
                }

                if (this.comprobanteType === 'citas') {
                    response = await axios.post('/api/comprobantes', {
                        ...comprobanteData,
                        citas: [this.selectedAppointmentId],
                        paciente_id: this.selectedPatient.id
                    });
                } else if (this.comprobanteType === 'productos') {
                    response = await axios.post('/api/comprobantes', {
                        ...comprobanteData,
                        productos_comprobante_id: this.selectedProductoComprobanteId
                    });
                }

                if (response.data.error) {
                    alert('Error: ' + response.data.error);
                    return;
                }

                const comprobante = response.data.comprobante;
                this.$router.push({
                    path: '/facturacion',
                    query: { successMessage: `SE GENERO COMPROBANTE ${comprobante.serie}-${comprobante.correlativo.toString().padStart(8, '0')}` }
                });
            } catch (error) {
                console.error('Error generating comprobante:', error);
                alert('Error al generar el comprobante: ' + (error.response?.data?.error || error.message));
            }
        },
        // Delete a product purchase request and restore stock
        async deleteProductoComprobante(id) {
            if (!confirm('¿Está seguro de que desea eliminar esta solicitud? Se restaurará el stock de los productos.')) {
                return;
            }

            try {
                const response = await axios.delete(`/api/productos-comprobante/${id}`);
                
                if (response.data.error) {
                    alert('Error: ' + response.data.error);
                    return;
                }

                alert(response.data.message || 'Solicitud eliminada correctamente');
                
                // Clear selection if the deleted item was selected
                if (this.selectedProductoComprobanteId == id) {
                    this.selectedProductoComprobanteId = '';
                    this.selectedProductoComprobante = null;
                    this.productoComprobanteItems = [];
                }
                
                // Refresh the list
                this.fetchProductoComprobantes();
                
            } catch (error) {
                console.error('Error deleting producto comprobante:', error);
                alert('Error al eliminar la solicitud: ' + (error.response?.data?.error || error.message));
            }
        },
        // Get the label for the product type based on its code
        getTipoProductoLabel(tipo) {
            switch (tipo) {
                case 'l': return 'Lentes de Sol';
                case 'm': return 'Montura';
                case 'c': return 'Lentes de Contacto';
                case 'u': return 'Lunas';
                default: return 'N/A';
            }
        },

        // Handle tipo de comprobante change
        onTipoComprobanteChange() {
            // Clear RUC and Razón Social fields when switching from Factura to Boleta
            if (this.comprobante.tipo === 'b') {
                this.comprobante.numero_ruc = '';
                this.comprobante.razon_social = '';
                this.rucError = '';
            }
        },

        // Validate RUC format (11 digits)
        validateRuc() {
            if (this.comprobante.numero_ruc && !/^[0-9]{11}$/.test(this.comprobante.numero_ruc)) {
                this.rucError = 'El RUC debe tener exactamente 11 dígitos';
            } else {
                this.rucError = '';
            }
        }
    }
}
</script>

<style scoped>
.select-wrapper {
  position: relative;
  width: 100%;
}

.select-wrapper select {
  width: 100%;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  padding-right: 30px;
  background-color: #fff;  /* Ensure background is white */
  cursor: pointer;
}

.select-wrapper option {
  cursor: pointer !important;
  appearance: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
}

.select-arrow {
  position: absolute;
  right: 15px; /* Adjusted to align properly */
  top: 69%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #6c757d;
}

.table th, .table td {
  vertical-align: middle; /* Center align the content vertically */
}

.clickable-row {
  cursor: pointer;
}

.clickable-row:hover {
  background-color: #f8f9fa;
}

.pointer {
  cursor: pointer;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
}
</style>
