<template>
  <div>
    <div class="mb-4">
      <button @click="goBack" class="btn btn-link">← Regresar</button>
    </div>
    <h2>Gestión de Stock</h2>
    <button @click="showCreateForm" class="btn btn-primary mb-3">Crear Producto</button>
    <button @click="resetFilters" class="btn btn-secondary mb-3 ms-2">Resetear Filtros</button>

    <div v-if="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="closeAlert" aria-label="Close"></button>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Imagen</th>
          <th>Producto</th>
          <th>Precio</th>
          <th>Tipo de Producto</th>
          <th>Proveedor</th>
          <th>Acciones</th>
        </tr>
        <tr>
          <th></th>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.producto" @input="applyFilters" class="form-control" placeholder="Filtrar por Producto">
              <button v-if="filters.producto" @click="clearFilter('producto')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th>
            <div class="position-relative">
              <input type="number" v-model="filters.precio" @input="applyFilters" class="form-control" placeholder="Filtrar por Precio">
              <button v-if="filters.precio" @click="clearFilter('precio')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th>
            <div class="position-relative select-wrapper">
              <select v-model="filters.tipo_producto" @change="applyFilters" class="form-control">
                <option value="">Todos</option>
                <option value="l">Lentes de Sol</option>
                <option value="m">Montura</option>
                <option value="c">Lentes de Contacto</option>
                <option value="u">Lunas</option>
              </select>
              <i class="fas fa-chevron-down select-arrow"></i>
            </div>
          </th>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.proveedor" @input="applyFilters" class="form-control" placeholder="Filtrar por Proveedor">
              <button v-if="filters.proveedor" @click="clearFilter('proveedor')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="6" class="text-center">Cargando...</td>
        </tr>
        <tr v-else-if="!items || items.length === 0">
          <td colspan="6" class="text-center">No hay productos en el stock.</td>
        </tr>
        <tr v-else v-for="item in items" :key="item.id">
          <td><img :src="`${baseUrl}/images/stock/${item.imagen}`" alt="Producto" style="height: 50px;"></td>
          <td>{{ item.producto }}</td>
          <td>S/. {{ item.precio }}</td>
          <td>{{ getTipoProductoLabel(item.tipo_producto) }}</td>
          <td>{{ item.proveedor ? item.proveedor.razon_social : 'N/A' }}</td>
          <td>
            <button @click="editItem(item)" class="btn btn-warning btn-sm me-2">
              <i class="fas fa-pencil-alt"></i>
            </button>
            <button @click="confirmDelete(item.id)" class="btn btn-danger btn-sm">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <nav v-if="pagination.total > pagination.per_page">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.prev_page_url)">Anterior</a>
        </li>
        <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.next_page_url)">Siguiente</a>
        </li>
      </ul>
    </nav>

    <!-- Modal -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="stockModalLabel">{{ isEditing ? 'Editar Producto' : 'Crear Producto' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="tipo_producto" class="form-label">Tipo de Producto*:</label>
                <div class="position-relative select-wrapper">
                  <select v-model="form.tipo_producto" id="tipo_producto" class="form-control" required>
                    <option value="" disabled selected>Seleccione un tipo de producto</option>
                    <option value="l">Lentes de Sol</option>
                    <option value="m">Montura</option>
                    <option value="c">Lentes de Contacto</option>
                    <option value="u">Lunas</option>
                  </select>
                  <i class="fas fa-chevron-down select-arrow"></i>
                </div>
              </div>
              <div class="mb-3">
                <label for="producto" class="form-label">Producto*:</label>
                <input type="text" v-model="form.producto" id="producto" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="precio" class="form-label">Precio*:</label>
                <div class="input-group">
                  <span class="input-group-text">S/.</span>
                  <input type="number" v-model="form.precio" id="precio" class="form-control" step="0.01" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="proveedor" class="form-label">Proveedor*:</label>
                <div class="position-relative select-wrapper">
                  <select v-model="form.id_proveedor" id="proveedor" class="form-control" required>
                    <option value="" disabled selected>Seleccione un proveedor</option>
                    <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                      {{ proveedor.razon_social }}
                    </option>
                  </select>
                  <i class="fas fa-chevron-down select-arrow"></i>
                </div>
              </div>
              <div class="mb-3">
                <label for="imagen" class="form-label">Imagen{{ isEditing ? ' (dejar en blanco para mantener la actual)' : '*' }}:</label>
                <input 
                  type="file" 
                  @change="handleImageUpload" 
                  id="imagen" 
                  ref="fileInput"
                  class="form-control" 
                  :required="!isEditing"
                >
              </div>
              <div v-if="imagePreview" class="mb-3">
                <img :src="imagePreview" alt="Preview" style="max-height: 200px;" class="preview-image">
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">{{ isEditing ? 'Actualizar' : 'Guardar' }}</button>
                <button type="button" @click="closeModal" class="btn btn-secondary">Cerrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';

export default {
  data() {
    return {
      baseUrl: window.location.origin,
      items: [],
      proveedores: [],
      loading: true,
      form: {
        tipo_producto: '',
        producto: '',
        precio: '',
        id_proveedor: '',
        imagen: null
      },
      imagePreview: null,
      showForm: false,
      isEditing: false,
      editingId: null,
      alertMessage: '',
      pagination: {
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        next_page_url: null,
        prev_page_url: null
      },
      filters: {
        producto: '',
        precio: '',
        tipo_producto: '',
        proveedor: ''
      }
    };
  },
  methods: {
    fetchItems(url = '/api/stock') {
      this.loading = true;
      const params = {
        producto: this.filters.producto || null,
        precio: this.filters.precio || null,
        tipo_producto: this.filters.tipo_producto || null,
        proveedor: this.filters.proveedor || null,
        page: this.pagination.current_page
      };
      
      axios.get(url, { params })
        .then(response => {
          console.log('Response:', response.data);
          this.items = response.data.data;
          this.pagination = {
            current_page: response.data.current_page,
            per_page: response.data.per_page,
            total: response.data.total,
            last_page: response.data.last_page,
            next_page_url: response.data.next_page_url,
            prev_page_url: response.data.prev_page_url
          };
        })
        .catch(error => {
          console.error('Error fetching items:', error);
          this.alertMessage = 'Error al cargar los productos.';
        })
        .finally(() => {
          this.loading = false;
        });
    },
    fetchProveedores() {
      axios.get('/api/proveedores')
        .then(response => {
          this.proveedores = response.data.data;
        })
        .catch(error => {
          console.error('Error fetching proveedores:', error);
        });
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      this.form.imagen = file;
      this.imagePreview = URL.createObjectURL(file);
    },
    showCreateForm() {
      this.resetForm();
      this.showForm = true;
      this.isEditing = false;
      new Modal(document.getElementById('stockModal')).show();
    },
    editItem(item) {
      this.form = {
        producto: item.producto,
        precio: item.precio,
        tipo_producto: item.tipo_producto,
        id_proveedor: item.id_proveedor,
        imagen: null
      };
      this.imagePreview = `${this.baseUrl}/images/stock/${item.imagen}`;
      this.showForm = true;
      this.isEditing = true;
      this.editingId = item.id;
      new Modal(document.getElementById('stockModal')).show();
    },
    async submitForm() {
      const formData = new FormData();
      formData.append('producto', this.form.producto);
      formData.append('precio', this.form.precio);
      formData.append('tipo_producto', this.form.tipo_producto);
      formData.append('id_proveedor', this.form.id_proveedor);
      
      if (this.form.imagen instanceof File) {
        formData.append('imagen', this.form.imagen);
      }

      try {
        let response;
        if (this.isEditing) {
          formData.append('_method', 'PUT');
          response = await axios.post(`/api/stock/${this.editingId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        } else {
          response = await axios.post('/api/stock', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        }

        this.alertMessage = this.isEditing ? 'Producto actualizado con éxito.' : 'Producto creado con éxito.';
        this.fetchItems();
        this.resetForm();
        this.closeModal();
        setTimeout(() => this.alertMessage = '', 5000);
      } catch (error) {
        console.error('Error details:', error.response?.data);
        this.alertMessage = error.response?.data?.error || 'Error al ' + (this.isEditing ? 'actualizar' : 'crear') + ' el producto.';
      }
    },
    confirmDelete(id) {
      if (confirm('¿Está seguro de que desea eliminar este producto?')) {
        this.deleteItem(id);
      }
    },
    deleteItem(id) {
      axios.delete(`/api/stock/${id}`)
        .then(() => {
          this.fetchItems();
          this.alertMessage = 'Producto eliminado con éxito.';
          setTimeout(() => this.alertMessage = '', 5000);
        })
        .catch(error => console.error(error));
    },
    resetForm() {
      this.form = {
        tipo_producto: '',
        producto: '',
        precio: '',
        id_proveedor: '',
        imagen: null
      };
      this.imagePreview = null;
      this.isEditing = false;
      this.editingId = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    closeModal() {
      const modal = Modal.getInstance(document.getElementById('stockModal'));
      if (modal) {
        modal.hide();
        this.resetForm();
      }
    },
    changePage(url) {
      if (url) {
        const page = new URL(url).searchParams.get('page');
        this.pagination.current_page = page;
        this.fetchItems(url);
      }
    },
    applyFilters() {
      this.pagination.current_page = 1;
      this.fetchItems();
    },
    resetFilters() {
      this.filters = {
        producto: '',
        precio: '',
        tipo_producto: '',
        proveedor: ''
      };
      this.applyFilters();
    },
    clearFilter(filter) {
      this.filters[filter] = '';
      this.applyFilters();
    },
    closeAlert() {
      this.alertMessage = '';
    },
    goBack() {
      window.history.back();
    },
    getTipoProductoLabel(tipo) {
      switch (tipo) {
        case 'l':
          return 'Lentes de Sol';
        case 'm':
          return 'Montura';
        case 'c':
          return 'Lentes de Contacto';
        case 'u':
          return 'Lunas';
        default:
          return tipo;
      }
    }
  },
  mounted() {
    this.fetchItems();
    this.fetchProveedores();
  }
};
</script>

<style scoped>
.btn-clear {
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 0;
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-icon {
  width: 12px;
  height: 12px;
  object-fit: contain;
}

.preview-image {
  max-width: 100%;
  object-fit: contain;
}

/* Update select-wrapper styles to apply to all select inputs */
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
}

.select-arrow {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #6c757d;
}
</style>
