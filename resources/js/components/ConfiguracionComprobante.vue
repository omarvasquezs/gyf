<template>
  <div>
    <div class="mb-4">
      <button @click="goBack" class="btn btn-link">← Regresar</button>
    </div>
    <h2>Configuración de Comprobante</h2>
    
    <div v-if="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="closeAlert" aria-label="Close"></button>
    </div>

    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="closeErrorAlert" aria-label="Close"></button>
    </div>

    <div class="row">
      <!-- Configuration Form -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Datos de la Empresa</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveConfiguration">
              <div class="row mb-3">
                <div class="col-md-12">
                  <label for="company_name" class="form-label">Nombre de la Empresa *</label>
                  <input 
                    type="text" 
                    v-model="config.company_name" 
                    id="company_name" 
                    class="form-control" 
                    required
                    placeholder="G & F oftalmólogas. S.A.C."
                  >
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-12">
                  <label for="address" class="form-label">Dirección *</label>
                  <input 
                    type="text" 
                    v-model="config.address" 
                    id="address" 
                    class="form-control" 
                    required
                    placeholder="Calle almenara 124 interior 201 surquillo"
                  >
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="ruc" class="form-label">RUC *</label>
                  <input 
                    type="text" 
                    v-model="config.ruc" 
                    id="ruc" 
                    class="form-control" 
                    required
                    placeholder="20613814265"
                  >
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Teléfono *</label>
                  <input 
                    type="text" 
                    v-model="config.phone" 
                    id="phone" 
                    class="form-control" 
                    required
                    placeholder="940 213 168"
                  >
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="email" class="form-label">Correo Electrónico</label>
                  <input 
                    type="email" 
                    v-model="config.email" 
                    id="email" 
                    class="form-control"
                    placeholder="correo@empresa.com"
                  >
                </div>
                <div class="col-md-6">
                  <label for="website" class="form-label">Sitio Web</label>
                  <input 
                    type="url" 
                    v-model="config.website" 
                    id="website" 
                    class="form-control"
                    placeholder="https://www.empresa.com"
                  >
                </div>
              </div>

              <hr>

              <h6 class="mb-3">Configuración de Formato</h6>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="font_family" class="form-label">Fuente *</label>
                  <select v-model="config.font_family" id="font_family" class="form-control" required>
                    <option value="Courier New">Courier New (Monospace)</option>
                    <option value="Arial">Arial</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Helvetica">Helvetica</option>
                    <option value="Georgia">Georgia</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="font_size" class="form-label">Tamaño de Fuente *</label>
                  <input 
                    type="number" 
                    v-model="config.font_size" 
                    id="font_size" 
                    class="form-control" 
                    min="6" 
                    max="20" 
                    required
                  >
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="header_alignment" class="form-label">Alineación del Encabezado *</label>
                  <select v-model="config.header_alignment" id="header_alignment" class="form-control" required>
                    <option value="left">Izquierda</option>
                    <option value="center">Centro</option>
                    <option value="right">Derecha</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <div class="form-check mt-4">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      v-model="config.show_logo" 
                      id="show_logo"
                    >
                    <label class="form-check-label" for="show_logo">
                      Mostrar Logo
                    </label>
                  </div>
                </div>
              </div>

              <div v-if="config.show_logo" class="row mb-3">
                <div class="col-md-12">
                  <label for="logo" class="form-label">Logo de la Empresa</label>
                  <input 
                    type="file" 
                    ref="logoFile"
                    id="logo" 
                    class="form-control" 
                    accept="image/*"
                    @change="handleLogoChange"
                  >
                  <div class="form-text">Formato: JPG, PNG, GIF. Tamaño máximo: 2MB</div>
                  
                  <div v-if="config.logo_path" class="mt-2">
                    <img 
                      :src="`/storage/${config.logo_path}`" 
                      alt="Logo actual" 
                      class="img-thumbnail"
                      style="max-height: 100px;"
                    >
                    <button 
                      type="button" 
                      @click="removeLogo" 
                      class="btn btn-sm btn-danger ms-2"
                    >
                      Eliminar Logo
                    </button>
                  </div>
                </div>
              </div>

              <div class="text-end">
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                  {{ loading ? 'Guardando...' : 'Guardar Configuración' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Preview -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Vista Previa</h5>
          </div>
          <div class="card-body">
            <div 
              class="preview-container border p-3"
              :style="{
                fontFamily: config.font_family,
                fontSize: config.font_size + 'px',
                textAlign: config.header_alignment
              }"
            >
              <div v-if="config.show_logo && config.logo_path" class="mb-2">
                <img 
                  :src="`/storage/${config.logo_path}`" 
                  alt="Logo" 
                  style="max-height: 40px;"
                >
              </div>
              
              <h6 class="mb-1" :style="{ fontSize: (config.font_size + 2) + 'px' }">
                {{ config.company_name }}
              </h6>
              <p class="mb-1">Dirección: {{ config.address }}</p>
              <p class="mb-1">RUC: {{ config.ruc }}</p>
              <p class="mb-1">Teléfono: {{ config.phone }}</p>
              <p v-if="config.email" class="mb-1">Email: {{ config.email }}</p>
              <p v-if="config.website" class="mb-0">Web: {{ config.website }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      config: {
        company_name: '',
        address: '',
        ruc: '',
        phone: '',
        email: '',
        website: '',
        font_family: 'Courier New',
        font_size: 8,
        header_alignment: 'center',
        show_logo: false,
        logo_path: null
      },
      loading: false,
      alertMessage: '',
      errorMessage: '',
      selectedLogo: null
    };
  },
  mounted() {
    this.loadConfiguration();
  },
  methods: {
    async loadConfiguration() {
      try {
        const response = await axios.get('/api/comprobante-config');
        this.config = { ...this.config, ...response.data };
      } catch (error) {
        console.error('Error loading configuration:', error);
        this.errorMessage = 'Error al cargar la configuración';
      }
    },

    handleLogoChange(event) {
      const file = event.target.files[0];
      if (file) {
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
          this.errorMessage = 'El archivo es demasiado grande. Máximo 2MB.';
          this.$refs.logoFile.value = '';
          return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
          this.errorMessage = 'Por favor seleccione un archivo de imagen válido.';
          this.$refs.logoFile.value = '';
          return;
        }
        
        this.selectedLogo = file;
      }
    },

    async saveConfiguration() {
      this.loading = true;
      this.errorMessage = '';
      this.alertMessage = '';

      try {
        const formData = new FormData();
        
        // Add all config fields
        Object.keys(this.config).forEach(key => {
          if (key !== 'logo_path' && this.config[key] !== null) {
            formData.append(key, this.config[key]);
          }
        });

        // Add logo file if selected
        if (this.selectedLogo) {
          formData.append('logo', this.selectedLogo);
        }

        const response = await axios.post('/api/comprobante-config', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        this.config = { ...this.config, ...response.data.config };
        this.alertMessage = response.data.message;
        this.selectedLogo = null;
        if (this.$refs.logoFile) {
          this.$refs.logoFile.value = '';
        }

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.alertMessage = '';
        }, 5000);

      } catch (error) {
        console.error('Error saving configuration:', error);
        this.errorMessage = error.response?.data?.error || 'Error al guardar la configuración';
      } finally {
        this.loading = false;
      }
    },

    async removeLogo() {
      if (!confirm('¿Está seguro de que desea eliminar el logo?')) {
        return;
      }

      try {
        const response = await axios.delete('/api/comprobante-config/logo');
        this.config.logo_path = null;
        this.config.show_logo = false;
        this.alertMessage = response.data.message;

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.alertMessage = '';
        }, 5000);

      } catch (error) {
        console.error('Error removing logo:', error);
        this.errorMessage = 'Error al eliminar el logo';
      }
    },

    goBack() {
      window.history.back();
    },

    closeAlert() {
      this.alertMessage = '';
    },

    closeErrorAlert() {
      this.errorMessage = '';
    }
  }
};
</script>

<style scoped>
.preview-container {
  background-color: #f8f9fa;
  border-radius: 4px;
  min-height: 200px;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.form-check {
  padding-top: 0.375rem;
}

.img-thumbnail {
  max-width: 100px;
}
</style>
