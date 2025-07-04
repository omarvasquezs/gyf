<template>
  <div>
    <div class="mb-4">
      <button @click="goBack" class="btn btn-link">← Regresar</button>
    </div>
    <h2>Gestión de Stock</h2>
    <div class="d-flex align-items-center gap-2 mb-3">
      <button @click="showCreateForm" class="btn btn-primary">Crear Producto</button>
      <button @click="resetFilters" class="btn btn-secondary ms-2">Resetear Filtros</button>
      <div class="import-stock-box ms-2 d-flex align-items-center gap-2 p-2 bg-light rounded shadow-sm">
        <label class="mb-0 fw-bold" for="importFile"><i class="fas fa-file-import me-1"></i>Importar Stock:</label>
        <input type="file" ref="importFile" id="importFile" @change="onFileChange" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="form-control form-control-sm" style="width:220px;" />
        <button type="button" class="btn btn-success btn-sm" @click="importStock" :disabled="importing">
          <i class="fas fa-upload me-1"></i>Importar
        </button>
        <button type="button" class="btn btn-info btn-sm" @click="downloadFormat">
          <i class="fas fa-download me-1"></i>Descargar Formato
        </button>
      </div>
    </div>
    <div v-if="importResult" class="alert mt-2 alert-dismissible fade show" :class="importResult.errors && importResult.errors.length ? 'alert-warning' : 'alert-success'" role="alert">
      <div v-if="importResult.imported">Importados: {{ importResult.imported }}</div>
      <div v-if="importResult && Array.isArray(importResult.errors) && importResult.errors.length">
        <strong>Errores:</strong>
        <ul>
          <li v-for="(err, idx) in importResult.errors" :key="idx">
            Fila: {{ (err.row && err.row.codigo) ? err.row.codigo : 'N/A' }} - {{ Array.isArray(err.errors) ? err.errors.join(', ') : err.errors }}
          </li>
        </ul>
      </div>
      <button type="button" class="btn-close" @click="importResult = null" aria-label="Close"></button>
    </div>

    <div v-if="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="closeAlert" aria-label="Close"></button>
    </div>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Código</th>
          <th>Tipo de Producto</th>
          <th>Descripción</th>
          <th>Marca</th>
          <th>Material</th>
          <th>Precio</th>
          <th>En Stock</th>
          <th>Acciones</th>
        </tr>
        <tr>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.codigo" @input="applyFilters" class="form-control" placeholder="Filtrar por Código">
              <button v-if="filters.codigo" @click="clearFilter('codigo')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th>
            <div class="position-relative select-wrapper" style="min-width:180px;">
              <div @click="showTipoProductoDropdown = !showTipoProductoDropdown" class="form-control d-flex align-items-center justify-content-between" style="cursor:pointer;">
                <span>
                  <span v-if="filters.tipo_producto.length === 0">Todos</span>
                  <span v-else>
                    {{ filters.tipo_producto.map(getTipoProductoLabel).join(', ') }}
                  </span>
                </span>
                <i class="fas fa-chevron-down select-arrow" :class="{ 'rotate-180': showTipoProductoDropdown }"></i>
              </div>
              <div v-if="showTipoProductoDropdown" class="dropdown-menu show p-2" style="width:100%;min-width:170px;max-width:250px;">
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" id="tipo_todos" :checked="filters.tipo_producto.length === 0" @change="selectAllTipoProducto">
                  <label class="form-check-label" for="tipo_todos">Todos</label>
                </div>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" id="tipo_l" value="l" :checked="filters.tipo_producto.includes('l')" @change="toggleTipoProducto('l')">
                  <label class="form-check-label" for="tipo_l">Lentes de Sol</label>
                </div>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" id="tipo_m" value="m" :checked="filters.tipo_producto.includes('m')" @change="toggleTipoProducto('m')">
                  <label class="form-check-label" for="tipo_m">Montura</label>
                </div>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" id="tipo_c" value="c" :checked="filters.tipo_producto.includes('c')" @change="toggleTipoProducto('c')">
                  <label class="form-check-label" for="tipo_c">Lentes de Contacto</label>
                </div>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" id="tipo_u" value="u" :checked="filters.tipo_producto.includes('u')" @change="toggleTipoProducto('u')">
                  <label class="form-check-label" for="tipo_u">Lunas</label>
                </div>
              </div>
            </div>
          </th>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.descripcion" @input="applyFilters" class="form-control" placeholder="Filtrar por Descripción">
              <button v-if="filters.descripcion" @click="clearFilter('descripcion')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.marca" @input="applyFilters" class="form-control" placeholder="Filtrar por Marca">
              <button v-if="filters.marca" @click="clearFilter('marca')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.material" @input="applyFilters" class="form-control" placeholder="Filtrar por Material">
              <button v-if="filters.material" @click="clearFilter('material')" class="btn-clear">
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
          <th></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="8" class="text-center">Cargando...</td>
        </tr>
        <tr v-else-if="!items || items.length === 0">
          <td colspan="8" class="text-center">No hay productos en el stock.</td>
        </tr>
        <tr v-else v-for="item in items" :key="item.id">
          <td>{{ item.codigo || 'N/A' }}</td>
          <td>{{ getTipoProductoLabel(item.tipo_producto) }}</td>
          <td>{{ item.descripcion || 'Sin descripción' }}</td>
          <td>{{ item.marca ? item.marca.marca : 'N/A' }}</td>
          <td>{{ item.material ? item.material.material : 'N/A' }}</td>
          <td>S/. {{ item.precio }}</td>
          <td class="text-center">
            <span class="badge rounded-pill" :class="item.tipo_producto === 'u' ? 'bg-primary' : (item.num_stock > 0 ? 'bg-success' : 'bg-danger')">
              {{ item.tipo_producto === 'u' ? '∞' : item.num_stock }}
            </span>
          </td>
          <td class="actions-cell-custom">
            <div class="action-buttons-grid">
              <button @click="viewDetails(item)" class="btn btn-info btn-sm" title="Ver detalles">
                <i class="fas fa-eye"></i>
              </button>
              <button @click="editItem(item)" class="btn btn-warning btn-sm" title="Editar">
                <i class="fas fa-pencil-alt"></i>
              </button>
              <button @click="confirmDelete(item.id)" class="btn btn-danger btn-sm" title="Eliminar">
                <i class="fas fa-trash-alt"></i>
              </button>
              <button @click="agregarProducto(item)" class="btn btn-success btn-sm" title="Agregar al carrito"
                :disabled="item.tipo_producto !== 'u' && item.num_stock <= 0">
                <i class="fas fa-cart-plus"></i>
              </button>
            </div>
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
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="stockModalLabel">{{ isEditing ? 'Editar Producto' : 'Crear Producto' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="tipo_producto" class="form-label">Tipo de Producto*:</label>
                  <div class="position-relative select-wrapper">
                    <select v-model="form.tipo_producto" id="tipo_producto" class="form-control" required @change="handleTipoProductoChange">
                      <option value="" disabled selected>Seleccione un tipo de producto</option>
                      <option value="l">Lentes de Sol</option>
                      <option value="m">Montura</option>
                      <option value="c">Lentes de Contacto</option>
                      <option value="u">Lunas</option>
                    </select>
                    <i class="fas fa-chevron-down select-arrow"></i>
                  </div>
                </div>
                <div class="col-md-6" v-if="form.tipo_producto !== 'u' && form.tipo_producto !== ''">
                  <label for="num_stock" class="form-label">Cantidad en Stock*:</label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="fas fa-boxes"></i>
                    </span>
                    <input type="number" v-model="form.num_stock" id="num_stock" class="form-control" min="0" :required="form.tipo_producto !== 'u' && form.tipo_producto !== ''">
                  </div>
                </div>
              </div>
              <template v-if="form.tipo_producto === 'u'">
                <div class="row mb-3">
                  <div class="col-12">
                    <label for="descripcion_luna" class="form-label">Descripción:</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="fas fa-tag"></i>
                      </span>
                      <input type="text" v-model="form.descripcion" id="descripcion_luna" class="form-control" placeholder="Ingrese descripción">
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="codigo" class="form-label">Código:</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="fas fa-barcode"></i>
                      </span>
                      <input type="text" v-model="form.codigo" id="codigo" class="form-control" placeholder="Ingrese código">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="id_marca" class="form-label">Marca:</label>
                    <div class="d-flex">
                      <div class="position-relative select-wrapper flex-grow-1">
                        <select v-model="form.id_marca" id="id_marca" class="form-control">
                          <option value="">Seleccione una marca</option>
                          <option v-for="marca in marcas" :key="marca.id" :value="marca.id">{{ marca.marca }}</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                      </div>
                      <button type="button" @click="showMarcaForm" class="btn btn-sm btn-outline-primary ms-2" title="Crear nueva marca">
                        <i class="fas fa-plus"></i>
                      </button>
                      <button type="button" @click="editSelectedMarca" class="btn btn-sm ms-2"
                        :class="form.id_marca ? 'btn-outline-warning' : 'btn-outline-secondary'"
                        :disabled="!form.id_marca" title="Editar marca seleccionada">
                        <i class="fas fa-pencil-alt"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="id_material" class="form-label">Material:</label>
                    <div class="d-flex">
                      <div class="position-relative select-wrapper flex-grow-1">
                        <select v-model="form.id_material" id="id_material" class="form-control">
                          <option value="">Seleccione un material</option>
                          <option v-for="material in materiales" :key="material.id" :value="material.id">{{ material.material }}</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                      </div>
                      <button type="button" @click="showMaterialForm" class="btn btn-sm btn-outline-primary ms-2" title="Crear nuevo material">
                        <i class="fas fa-plus"></i>
                      </button>
                      <button type="button" @click="editSelectedMaterial" class="btn btn-sm ms-2"
                        :class="form.id_material ? 'btn-outline-warning' : 'btn-outline-secondary'"
                        :disabled="!form.id_material" title="Editar material seleccionado">
                        <i class="fas fa-pencil-alt"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="precio" class="form-label">Precio*:</label>
                    <div class="input-group">
                      <span class="input-group-text">S/.</span>
                      <input type="number" v-model="form.precio" id="precio" class="form-control" step="0.01" required>
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="id_tipo_luna" class="form-label">Tipo de Lunas*:</label>
                    <div class="position-relative select-wrapper">
                      <select v-model="form.id_tipo_luna" id="id_tipo_luna" class="form-control" required>
                        <option value="" disabled>Seleccione tipo de lunas</option>
                        <option v-for="tipo in tiposLuna" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
                      </select>
                      <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="id_diseno_luna" class="form-label">Diseño*:</label>
                    <div class="position-relative select-wrapper">
                      <select v-model="form.id_diseno_luna" id="id_diseno_luna" class="form-control" required>
                        <option value="" disabled>Seleccione diseño</option>
                        <option v-for="diseno in disenosLuna" :key="diseno.id" :value="diseno.id">{{ diseno.nombre }}</option>
                      </select>
                      <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="indice" class="form-label">Índice*:</label>
                    <div class="position-relative select-wrapper">
                      <select v-model="form.indice" id="indice" class="form-control" required>
                        <option value="" disabled>Seleccione índice</option>
                        <option v-for="indice in indicesLuna" :key="indice" :value="indice">{{ indice }}</option>
                      </select>
                      <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="id_laboratorio_luna" class="form-label">Laboratorio*:</label>
                    <div class="position-relative select-wrapper">
                      <select v-model="form.id_laboratorio_luna" id="id_laboratorio_luna" class="form-control" required>
                        <option value="" disabled>Seleccione laboratorio</option>
                        <option v-for="lab in laboratoriosLuna" :key="lab.id" :value="lab.id">{{ lab.nombre }}</option>
                      </select>
                      <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                  </div>
                </div>
              </template>
              <template v-else>
                <div class="row mb-3">
                  <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="fas fa-tag"></i>
                      </span>
                      <input type="text" v-model="form.descripcion" id="descripcion" class="form-control" placeholder="Ingrese descripción">
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="codigo" class="form-label">Código:</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="fas fa-barcode"></i>
                      </span>
                      <input type="text" v-model="form.codigo" id="codigo" class="form-control" placeholder="Ingrese código">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="precio" class="form-label">Precio*:</label>
                    <div class="input-group">
                      <span class="input-group-text">S/.</span>
                      <input type="number" v-model="form.precio" id="precio" class="form-control" step="0.01" required placeholder="Ingrese precio">
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="genero" class="form-label">Género:</label>
                    <div class="position-relative select-wrapper">
                      <select v-model="form.genero" id="genero" class="form-control">
                        <option value="" selected>Seleccione un género</option>
                        <option value="H">Hombre</option>
                        <option value="M">Mujer</option>
                        <option value="N">Niño</option>
                        <option value="U">Unisex</option>
                      </select>
                      <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label for="material" class="form-label">Material:</label>
                    <div class="d-flex">
                      <div class="position-relative select-wrapper flex-grow-1">
                        <select v-model="form.id_material" id="material" class="form-control">
                          <option value="" disabled selected>Seleccione un material</option>
                          <option v-for="material in materiales" :key="material.id" :value="material.id">
                            {{ material.material }}
                          </option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                      </div>
                      <button type="button" @click="showMaterialForm" class="btn btn-sm btn-outline-primary ms-2" title="Crear nuevo material">
                        <i class="fas fa-plus"></i>
                      </button>
                      <button type="button" @click="editSelectedMaterial" class="btn btn-sm ms-2" 
                        :class="form.id_material ? 'btn-outline-warning' : 'btn-outline-secondary'"
                        :disabled="!form.id_material" title="Editar material seleccionado">
                        <i class="fas fa-pencil-alt"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="fecha_compra" class="form-label">Fecha de Compra:</label>
                    <input type="date" v-model="form.fecha_compra" id="fecha_compra" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label for="marca" class="form-label">Marca*:</label>
                    <div class="d-flex">
                      <div class="position-relative select-wrapper flex-grow-1">
                        <select v-model="form.id_marca" id="marca" class="form-control" :required="form.tipo_producto !== 'u' && form.tipo_producto !== ''">
                          <option value="" disabled selected>Seleccione una marca</option>
                          <option v-for="marca in marcas" :key="marca.id" :value="marca.id">
                            {{ marca.marca }}
                          </option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                      </div>
                      <button type="button" @click="showMarcaForm" class="btn btn-sm btn-outline-primary ms-2" title="Crear nueva marca">
                        <i class="fas fa-plus"></i>
                      </button>
                      <button type="button" @click="editSelectedMarca" class="btn btn-sm ms-2" 
                        :class="form.id_marca ? 'btn-outline-warning' : 'btn-outline-secondary'"
                        :disabled="!form.id_marca" title="Editar marca seleccionada">
                        <i class="fas fa-pencil-alt"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="imagen" class="form-label">Imagen{{ isEditing ? ' (dejar en blanco para mantener la actual)' : '' }}:</label>
                  <input 
                    type="file" 
                    @change="handleImageUpload" 
                    id="imagen" 
                    ref="fileInput"
                    class="form-control" 
                  >
                </div>
                <div v-if="imagePreview" class="mb-3">
                  <img :src="imagePreview" alt="Preview" style="max-height: 200px;" class="preview-image">
                </div>
              </template>
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">{{ isEditing ? 'Actualizar' : 'Guardar' }}</button>
                <button type="button" @click="closeModal" class="btn btn-secondary">Cerrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Marca Modal -->
    <div class="modal fade" id="marcaModal" tabindex="-1" aria-labelledby="marcaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="marcaModalLabel">{{ isEditingMarca ? 'Editar Marca' : 'Crear Marca' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitMarcaForm">
              <div v-if="marcaFormErrors.length" class="alert alert-danger">
                <ul class="mb-0">
                  <li v-for="(error, index) in marcaFormErrors" :key="index">{{ error }}</li>
                </ul>
              </div>
              
              <div class="mb-3">
                <label for="marcaNombre" class="form-label">Nombre de la Marca*:</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="fas fa-trademark"></i>
                  </span>
                  <input type="text" v-model="marcaForm.marca" id="marcaNombre" class="form-control" required>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="proveedores" class="form-label">Proveedores:</label>
                <div class="proveedor-selection">
                  <!-- Selected Proveedores -->
                  <div class="selected-proveedores mb-2">
                    <div v-for="(proveedor, index) in selectedProveedores" :key="proveedor.id" 
                         class="badge bg-info px-2 py-1 me-1 mb-1 selected-proveedor">
                      {{ proveedor.razon_social }}
                      <button type="button" @click="removeProveedor(index)" class="btn-close btn-close-white ms-1" 
                              aria-label="Eliminar" style="font-size: 0.5rem;">
                      </button>
                    </div>
                  </div>
                  
                  <!-- Search Input -->
                  <div class="position-relative">
                    <input 
                      type="text" 
                      v-model="proveedorSearch"
                      @input="searchProveedores"
                      @focus="showAllProveedores"
                      @keydown.down.prevent="navigateResults('down')"
                      @keydown.up.prevent="navigateResults('up')"
                      @keydown.enter.prevent="selectProveedor(selectedResultIndex)"
                      @keydown.escape="showProveedorResults = false"
                      class="form-control" 
                      placeholder="Buscar proveedores..."
                      autocomplete="off"
                    >
                    
                    <!-- Dropdown Results -->
                    <div v-if="showProveedorResults && filteredProveedores.length > 0" class="proveedor-results">
                      <div 
                        v-for="(proveedor, index) in filteredProveedores" 
                        :key="proveedor.id" 
                        class="proveedor-result-item"
                        :class="{ 'active': index === selectedResultIndex }"
                        @click="addProveedor(proveedor)"
                        @mouseover="selectedResultIndex = index"
                      >
                        {{ proveedor.razon_social }}
                      </div>
                    </div>
                    
                    <!-- No Results Message -->
                    <div v-if="showProveedorResults && proveedorSearch && filteredProveedores.length === 0" class="proveedor-no-results">
                      No se encontraron proveedores
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">{{ isEditingMarca ? 'Actualizar' : 'Guardar' }}</button>
                <button type="button" @click="closeMarcaModal" class="btn btn-secondary">Cerrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Material Modal -->
    <div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="materialModalLabel">{{ isEditingMaterial ? 'Editar Material' : 'Crear Material' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitMaterialForm">
              <div v-if="materialFormErrors.length" class="alert alert-danger">
                <ul class="mb-0">
                  <li v-for="(error, index) in materialFormErrors" :key="index">{{ error }}</li>
                </ul>
              </div>
              
              <div class="mb-3">
                <label for="materialNombre" class="form-label">Nombre del Material*:</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i class="fas fa-cube"></i>
                  </span>
                  <input type="text" v-model="materialForm.material" id="materialNombre" class="form-control" required>
                </div>
              </div>
              
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">{{ isEditingMaterial ? 'Actualizar' : 'Guardar' }}</button>
                <button type="button" @click="closeMaterialModal" class="btn btn-secondary">Cerrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailsModalLabel">Detalles del Producto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="detailItem">
              <div class="row mb-4">
                <div class="col-md-6 text-center mb-3">
                  <img v-if="detailItem.imagen" :src="`${baseUrl}/images/stock/${detailItem.imagen}`" alt="Producto" class="img-fluid details-image" style="max-height: 250px;">
                  <div v-else class="no-image-placeholder">Sin imagen</div>
                </div>
                <div class="col-md-6">
                  <h4>{{ detailItem.descripcion || 'Sin descripción' }}</h4>
                  <p class="mb-1"><strong>Código:</strong> {{ detailItem.codigo || 'N/A' }}</p>
                  <p class="mb-1"><strong>Precio:</strong> S/. {{ detailItem.precio }}</p>
                  <p class="mb-1"><strong>Stock:</strong> {{ detailItem.num_stock }}</p>
                  <p class="mb-1"><strong>Tipo de Producto:</strong> {{ getTipoProductoLabel(detailItem.tipo_producto) }}</p>
                  <p class="mb-1"><strong>Género:</strong> {{ getGeneroLabel(detailItem.genero) }}</p>
                </div>
              </div>
              <hr>
              <div class="row">
                <div class="col-md-6">
                  <p class="mb-1"><strong>Material:</strong> {{ detailItem.material ? detailItem.material.material : 'N/A' }}</p>
                  <p class="mb-1"><strong>Fecha de Compra:</strong> {{ detailItem.fecha_compra || 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                  <p class="mb-1"><strong>Marca:</strong> {{ detailItem.marca ? detailItem.marca.marca : 'N/A' }}</p>
                  <p class="mb-1"><strong>ID del Producto:</strong> {{ detailItem.id }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Shopping Cart Icon Button -->
    <button @click="openCart" class="btn-cart-icon">
      <i class="fas fa-shopping-cart"></i>
      <span v-if="cartItemCount > 0" class="cart-item-count">{{ cartItemCount }}</span>
    </button>

    <!-- Cart Modal with Transition -->
    <transition name="fade">
      <div v-if="showCart" class="modal d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Carrito</h5>
              <button type="button" class="btn-close" aria-label="Close" @click="closeCart"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                <input type="text" v-model="nombres" class="form-control" id="nombres" placeholder="Ingrese nombres">
              </div>
              <div class="mb-3">
                <label for="dni_ce" class="form-label">DNI/CE</label>
                <input 
                  type="text" 
                  v-model="dni_ce" 
                  class="form-control" 
                  id="dni_ce" 
                  placeholder="Ingrese DNI/CE (8-9 dígitos)"
                  pattern="[0-9]{8,9}"
                  maxlength="9"
                  @input="validateDniCe"
                >
                <div v-if="dniCeError" class="text-danger small mt-1">{{ dniCeError }}</div>
              </div>
              <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" v-model="telefono" class="form-control" id="telefono" placeholder="Ingrese teléfono">
              </div>
              
              <!-- Total Amount Display -->
              <div class="mb-3">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                  <span><strong>Total:</strong> S/. {{ calculateTotal.toFixed(2) }}</span>
                  <span><strong>Items:</strong> {{ cartItemCount }}</span>
                </div>
              </div>
              
              <table v-if="cart.length" class="table table-bordered">
                <thead>
                  <tr>
                    <th><input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"></th>
                    <th>Producto</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Precio Total</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in cart" :key="item.id">
                    <td><input type="checkbox" v-model="selectedItems" :value="item.id"></td>
                    <td>{{ item.descripcion || 'Sin descripción' }}</td>
                    <td>
                      <input type="number" v-model.number="item.precio" min="0" step="0.01" class="form-control" 
                             style="width:100px;" @change="updatePrecioUnitario(index, $event)">
                    </td>
                    <td>
                      <template v-if="item.tipo_producto === 'u'">
                        1
                      </template>
                      <template v-else>
                        <input type="number" v-model.number="item.quantity" min="1" class="form-control"
                          style="width:100px;" @change="updateQuantity(index, $event)">
                      </template>
                    </td>
                    <td>S/. {{ (item.precio * item.quantity).toFixed(2) }}</td>
                    <td>
                      <div class="text-center">
                          <button @click="removerProducto(index)" class="btn btn-danger btn-sm" title="Eliminar">
                          <i class="fas fa-trash"></i>
                          </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p v-else class="mb-0">No hay productos</p>
            </div>
            <div class="modal-footer">
              <button v-if="selectedItems.length" type="button" class="btn btn-danger" @click="removerSeleccionados">Eliminar seleccionados</button>
              <button type="button" class="btn btn-primary" @click="procesarCart" v-if="selectedItems.length">Procesar</button>
              <button type="button" class="btn btn-secondary" @click="closeCart">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    </transition>
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
      marcas: [],
      materiales: [],
      loading: true,
      tiposLuna: [],
      disenosLuna: [],
      laboratoriosLuna: [],
      indicesLuna: [1.49, 1.50, 1.56, 1.59, 1.60, 1.607, 1.74],
      form: {
        tipo_producto: '',
        descripcion: '',
        precio: '',
        id_marca: '',
        imagen: null,
        codigo: '',
        genero: '',
        id_material: '',
        fecha_compra: '',
        num_stock: 0,
        // Lunas-specific
        id_tipo_luna: null,
        id_diseno_luna: null,
        id_laboratorio_luna: null,
        indice: null
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
        codigo: '',
        descripcion: '',
        precio: '',
        tipo_producto: [], // now an array for multi-select
        marca: '',
        material: ''
      },
      detailItem: null,
      marcaForm: {
        marca: '',
        proveedores: []
      },
      isEditingMarca: false,
      editingMarcaId: null,
      marcaFormErrors: [],
      allProveedores: [],
      proveedorSearch: '',
      filteredProveedores: [],
      selectedProveedores: [],
      showProveedorResults: false,
      selectedResultIndex: -1,
      debounceTimer: null,
      materialForm: {
        material: ''
      },
      isEditingMaterial: false,
      editingMaterialId: null,
      materialFormErrors: [],
      // Cart-related data
      cart: [],
      showCart: false,
      selectedItems: [],
      recentlyAddedProductId: null,
      nombres: '',
      dni_ce: '',
      telefono: '',
      dniCeError: '',
      showTipoProductoDropdown: false,
      importing: false,
      importFile: null,
      importResult: null,
    };
  },
  computed: {
    // Add cart-related computed properties
    cartItemCount() {
      return this.cart.reduce((total, item) => total + item.quantity, 0);
    },
    isAllSelected() {
      return this.cart.length && this.selectedItems.length === this.cart.length;
    },
    calculateTotal() {
      return this.cart.reduce((total, item) => total + (item.quantity * item.precio), 0);
    }
  },
  methods: {
    fetchItems(url = '/api/stock') {
      this.loading = true;
      const params = {
        codigo: this.filters.codigo || null,
        descripcion: this.filters.descripcion || null,
        precio: this.filters.precio || null,
        tipo_producto: this.filters.tipo_producto.length ? this.filters.tipo_producto.join(',') : null,
        marca: this.filters.marca || null,
        material: this.filters.material || null,
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
    fetchMarcas() {
      axios.get('/api/marcas', { params: { all: true, per_page: 1000 } }) // Request a high number per page to effectively get all
        .then(response => {
          this.marcas = response.data.data;
          console.log('Fetched all marcas:', this.marcas.length);
        })
        .catch(error => {
          console.error('Error fetching marcas:', error);
        });
    },
    fetchMateriales() {
      axios.get('/api/materiales', { params: { all: true, per_page: 1000 } }) // Request a high number per page to effectively get all
        .then(response => {
          this.materiales = response.data.data || response.data;
          console.log('Fetched all materiales:', this.materiales.length);
        })
        .catch(error => {
          console.error('Error fetching materiales:', error);
        });
    },
    async fetchTiposLuna() {
      try {
        const res = await axios.get('/api/tipo-lunas');
        this.tiposLuna = res.data.data || res.data;
      } catch (e) { console.error('Error cargando tipos de lunas', e); }
    },
    async fetchDisenosLuna() {
      try {
        const res = await axios.get('/api/diseno-lunas');
        this.disenosLuna = res.data.data || res.data;
      } catch (e) { console.error('Error cargando diseños de lunas', e); }
    },
    async fetchLaboratoriosLuna() {
      try {
        const res = await axios.get('/api/laboratorios-luna');
        this.laboratoriosLuna = res.data.data || res.data;
      } catch (e) { console.error('Error cargando laboratorios de lunas', e); }
    },
    handleTipoProductoChange() {
      if (this.form.tipo_producto === 'u') {
        // Set default values for Lunas
        this.form.num_stock = 1; // default Luna stock to 1
        // Only clear fields that are not relevant for Lunas
        this.form.genero = '';
        this.form.fecha_compra = '';
        this.form.imagen = null;
        // Reset luna-specific fields so placeholder option shows
        this.form.id_tipo_luna = '';
        this.form.id_diseno_luna = '';
        this.form.id_laboratorio_luna = '';
        this.form.indice = '';
      } else {
        // User selected non-luna product, clear luna fields
        this.form.id_tipo_luna = '';
        this.form.id_diseno_luna = '';
        this.form.id_laboratorio_luna = '';
        this.form.indice = '';
      }
    },
    showCreateForm() {
      this.resetForm();
      this.showForm = true;
      this.isEditing = false;
      this.fetchTiposLuna();
      this.fetchDisenosLuna();
      this.fetchLaboratoriosLuna();
      new Modal(document.getElementById('stockModal')).show();
    },
    editItem(item) {
      this.form = {
        descripcion: item.descripcion || '',
        precio: item.precio,
        tipo_producto: item.tipo_producto,
        id_marca: item.id_marca,
        imagen: null,
        codigo: item.codigo || '',
        genero: item.genero || '',
        id_material: item.id_material || '',
        fecha_compra: item.fecha_compra || '',
        num_stock: item.num_stock || 0
      };
      this.imagePreview = item.imagen ? `${this.baseUrl}/images/stock/${item.imagen}` : null;
      this.showForm = true;
      this.isEditing = true;
      this.editingId = item.id;
      this.fetchTiposLuna();
      this.fetchDisenosLuna();
      this.fetchLaboratoriosLuna();
      if (item.tipo_producto === 'u') {
        this.form.id_tipo_luna = item.id_tipo_luna;
        this.form.id_diseno_luna = item.id_diseno_luna;
        this.form.id_laboratorio_luna = item.id_laboratorio_luna;
        this.form.indice = item.indice_luna;
      } else {
        this.form.id_tipo_luna = null;
        this.form.id_diseno_luna = null;
        this.form.id_laboratorio_luna = null;
        this.form.indice = null;
      }
      new Modal(document.getElementById('stockModal')).show();
    },
    async submitForm() {
      const formData = new FormData();
      formData.append('descripcion', this.form.descripcion || '');
      formData.append('precio', this.form.precio);
      formData.append('tipo_producto', this.form.tipo_producto);
      formData.append('num_stock', this.form.num_stock); // Always include num_stock
      if (this.form.tipo_producto === 'u') {
        formData.append('id_tipo_luna', this.form.id_tipo_luna);
        formData.append('id_diseno_luna', this.form.id_diseno_luna);
        formData.append('id_laboratorio_luna', this.form.id_laboratorio_luna);
        formData.append('indice_luna', this.form.indice);
        // Add codigo, marca, and material for Lunas
        if (this.form.codigo) formData.append('codigo', this.form.codigo);
        if (this.form.id_marca) formData.append('id_marca', this.form.id_marca);
        if (this.form.id_material) formData.append('id_material', this.form.id_material);
      } else {
        formData.append('id_marca', this.form.id_marca);
        if (this.form.codigo) formData.append('codigo', this.form.codigo);
        if (this.form.genero) formData.append('genero', this.form.genero);
        if (this.form.id_material) formData.append('id_material', this.form.id_material);
        if (this.form.fecha_compra) formData.append('fecha_compra', this.form.fecha_compra);
        if (this.form.imagen instanceof File) {
          formData.append('imagen', this.form.imagen);
        }
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
        descripcion: '',
        precio: '',
        id_marca: '',
        imagen: null,
        codigo: '',
        genero: '',
        id_material: '',
        fecha_compra: '',
        num_stock: 0,
        id_tipo_luna: '',
        id_diseno_luna: '',
        id_laboratorio_luna: '',
        indice: ''
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
        codigo: '',
        descripcion: '',
        precio: '',
        tipo_producto: [],
        marca: '',
        material: ''
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
      if (this.cart.length > 0) {
        if (!window.confirm('Hay items en el carrito de compras, estas seguro de irse?, se borraran si se va.')) {
          return;
        }
        // Clear cart if confirmed
        this.cart = [];
        this.selectedItems = [];
      }
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
    },
    viewDetails(item) {
      // If we need to fetch additional details that might not be in the item
      axios.get(`/api/stock/${item.id}`)
        .then(response => {
          this.detailItem = response.data;
          new Modal(document.getElementById('detailsModal')).show();
        })
        .catch(error => {
          console.error('Error fetching item details:', error);
          this.alertMessage = 'Error al cargar los detalles del producto.';
        });
    },
    getGeneroLabel(genero) {
      switch (genero) {
        case 'H':
          return 'Hombre';
        case 'M':
          return 'Mujer';
        case 'N':
          return 'Niño';
        case 'U':
          return 'Unisex';
        default:
          return 'N/A';
      }
    },
    // Marca related methods
    showMarcaForm() {
      this.resetMarcaForm();
      this.isEditingMarca = false;
      
      // Ensure the stock modal is kept open by storing its instance
      this.stockModalInstance = Modal.getInstance(document.getElementById('stockModal'));
      
      // Create and show the marca modal
      this.marcaModalInstance = new Modal(document.getElementById('marcaModal'));
      this.marcaModalInstance.show();
    },
    
    resetMarcaForm() {
      this.marcaForm = {
        marca: '',
        proveedores: []
      };
      this.selectedProveedores = [];
      this.isEditingMarca = false;
      this.editingMarcaId = null;
      this.marcaFormErrors = [];
    },
    
    editSelectedMarca() {
      if (!this.form.id_marca) return;
      
      // Find the selected marca
      const selectedMarca = this.marcas.find(m => m.id === this.form.id_marca);
      if (!selectedMarca) return;
      
      // Set up form for editing
      this.marcaForm = { 
        marca: selectedMarca.marca,
        proveedores: selectedMarca.proveedores ? selectedMarca.proveedores.map(p => p.id) : []
      };
      
      this.selectedProveedores = selectedMarca.proveedores || [];
      this.isEditingMarca = true;
      this.editingMarcaId = selectedMarca.id;
      
      // Store stock modal instance
      this.stockModalInstance = Modal.getInstance(document.getElementById('stockModal'));
      
      // Show marca modal
      this.marcaModalInstance = new Modal(document.getElementById('marcaModal'));
      this.marcaModalInstance.show();
    },
    
    closeMarcaModal() {
      if (this.marcaModalInstance) {
        this.marcaModalInstance.hide();
      }
    },
    
    async submitMarcaForm() {
      this.marcaFormErrors = [];
      try {
        let response;
        
        if (this.isEditingMarca) {
          response = await axios.put(`/api/marcas/${this.editingMarcaId}`, this.marcaForm);
        } else {
          response = await axios.post('/api/marcas', this.marcaForm);
        }
        
        // Close the marca modal
        this.closeMarcaModal();
        
        // Show success message
        this.alertMessage = this.isEditingMarca ? 'Marca actualizada con éxito.' : 'Marca creada con éxito.';
        
        // Fetch updated list of marcas
        await this.fetchMarcas();
        
        // Set the newly created/edited marca as the selected marca
        const newMarca = response.data.id || (response.data.marca && response.data.marca.id) || this.editingMarcaId;
        if (newMarca) {
          this.form.id_marca = newMarca;
        }
        
        setTimeout(() => this.alertMessage = '', 5000);
      } catch (error) {
        console.error('Error submitting marca form:', error.response?.data);
        if (error.response && error.response.status === 422) {
          this.marcaFormErrors = Object.values(error.response.data.errors).flat();
        } else {
          this.marcaFormErrors = ['Ha ocurrido un error al procesar la solicitud.'];
        }
      }
    },
    
    // Proveedores methods for marca form
    fetchProveedores() {
      axios.get('/api/proveedores?all=true')
        .then(response => {
          this.allProveedores = response.data.data || response.data;
        })
        .catch(error => {
          console.error('Error fetching proveedores:', error);
        });
    },
    
    searchProveedores() {
      if (this.debounceTimer) clearTimeout(this.debounceTimer);
      
      this.debounceTimer = setTimeout(() => {
        const searchTerm = this.proveedorSearch.toLowerCase().trim();
        
        // Filter out already selected proveedores
        const selectedIds = this.selectedProveedores.map(p => p.id);
        
        // Show all available providers when search term is empty
        if (searchTerm === '') {
          this.filteredProveedores = this.allProveedores
            .filter(p => !selectedIds.includes(p.id))
            .slice(0, 10); // Limit to 10 results
        } else {
          this.filteredProveedores = this.allProveedores
            .filter(p => !selectedIds.includes(p.id) && 
                        (p.razon_social.toLowerCase().includes(searchTerm) || 
                         (p.ruc && p.ruc.includes(searchTerm))))
            .slice(0, 10); // Limit to 10 results
        }
        
        this.selectedResultIndex = this.filteredProveedores.length > 0 ? 0 : -1;
        this.showProveedorResults = true;
      }, 300);
    },
    
    showAllProveedores() {
      const selectedIds = this.selectedProveedores.map(p => p.id);
      
      this.filteredProveedores = this.allProveedores
        .filter(p => !selectedIds.includes(p.id))
        .slice(0, 10); // Limit to 10 results
      
      this.selectedResultIndex = this.filteredProveedores.length > 0 ? 0 : -1;
      this.showProveedorResults = true;
    },
    
    addProveedor(proveedor) {
      if (!this.selectedProveedores.some(p => p.id === proveedor.id)) {
        this.selectedProveedores.push(proveedor);
        this.marcaForm.proveedores = this.selectedProveedores.map(p => p.id);
      }
      this.proveedorSearch = '';
      this.showProveedorResults = false;
      this.filteredProveedores = [];
    },
    
    removeProveedor(index) {
      this.selectedProveedores.splice(index, 1);
      this.marcaForm.proveedores = this.selectedProveedores.map(p => p.id);
    },
    
    navigateResults(direction) {
      if (this.filteredProveedores.length === 0) return;
      
      if (direction === 'down') {
        this.selectedResultIndex = (this.selectedResultIndex + 1) % this.filteredProveedores.length;
      } else if (direction === 'up') {
        this.selectedResultIndex = this.selectedResultIndex <= 0 ? 
          this.filteredProveedores.length - 1 : this.selectedResultIndex - 1;
      }
    },
    
    selectProveedor(index) {
      if (index >= 0 && index < this.filteredProveedores.length) {
        this.addProveedor(this.filteredProveedores[index]);
      }
    },
    // Material related methods
    showMaterialForm() {
      this.resetMaterialForm();
      this.isEditingMaterial = false;
      
      // Ensure the stock modal is kept open by storing its instance
      this.stockModalInstance = Modal.getInstance(document.getElementById('stockModal'));
      
      // Create and show the material modal
      this.materialModalInstance = new Modal(document.getElementById('materialModal'));
      this.materialModalInstance.show();
    },
    
    resetMaterialForm() {
      this.materialForm = {
        material: ''
      };
      this.isEditingMaterial = false;
      this.editingMaterialId = null;
      this.materialFormErrors = [];
    },
    
    editSelectedMaterial() {
      if (!this.form.id_material) return;
      
      // Find the selected material
      const selectedMaterial = this.materiales.find(m => m.id === this.form.id_material);
      if (!selectedMaterial) return;
      
      // Set up form for editing
      this.materialForm = { 
        material: selectedMaterial.material
      };
      
      this.isEditingMaterial = true;
      this.editingMaterialId = selectedMaterial.id;
      
      // Store stock modal instance
      this.stockModalInstance = Modal.getInstance(document.getElementById('stockModal'));
      
      // Show material modal
      this.materialModalInstance = new Modal(document.getElementById('materialModal'));
      this.materialModalInstance.show();
    },
    
    closeMaterialModal() {
      if (this.materialModalInstance) {
        this.materialModalInstance.hide();
      }
    },
    
    async submitMaterialForm() {
      this.materialFormErrors = [];
      try {
        let response;
        if (this.isEditingMaterial) {
          response = await axios.put(`/api/materiales/${this.editingMaterialId}`, this.materialForm);
        } else {
          response = await axios.post('/api/materiales', this.materialForm);
        }
        this.fetchMateriales();
        this.closeMaterialModal();
        this.alertMessage = this.isEditingMaterial ? 'Material actualizado con éxito.' : 'Material creado con éxito.'; 
        setTimeout(() => this.alertMessage = '', 5000);
      } catch (error) {
        console.error('Error submitting material form:', error.response?.data);
        if (error.response && error.response.status === 422) {
          this.materialFormErrors = Object.values(error.response.data.errors).flat();
        } else {
          this.materialFormErrors = ['Ha ocurrido un error al procesar la solicitud.'];
        }
      }
    },
    // Cart-related methods
    agregarProducto(producto) {
      // Check if the product is already in the cart
      const existingProduct = this.cart.find(item => item.id === producto.id);

      if (producto.tipo_producto === 'u') {
        // Lunas: allow unlimited, just increment quantity or add
        if (existingProduct) {
          existingProduct.quantity += 1;
        } else {
          this.cart.push({ ...producto, quantity: 1 });
        }
      } else {
        // Other products: check stock
        if (existingProduct) {
          if (existingProduct.quantity < producto.num_stock) {
            existingProduct.quantity += 1;
          } else {
            this.alertMessage = 'No hay suficiente stock disponible.';
            setTimeout(() => { this.alertMessage = ''; }, 2000);
            return;
          }
        } else {
          if (producto.num_stock > 0) {
            this.cart.push({ ...producto, quantity: 1 });
          } else {
            this.alertMessage = 'No hay stock disponible.';
            setTimeout(() => { this.alertMessage = ''; }, 2000);
            return;
          }
        }
      }
      // Set recently added product ID for animation
      this.recentlyAddedProductId = producto.id;
      this.alertMessage = `"${producto.descripcion || 'Producto'}" agregado al carrito`;
      setTimeout(() => {
        this.recentlyAddedProductId = null;
        this.alertMessage = '';
      }, 1500);
    },
    
    removerProducto(index) {
      this.cart.splice(index, 1);
    },
    
    updatePrecioUnitario(index, event) {
      const newPrecio = parseFloat(event.target.value);
      if (!isNaN(newPrecio) && newPrecio >= 0) {
        this.cart[index].precio = newPrecio;
      } else {
        // Optionally, revert to old value or show an error
        event.target.value = this.cart[index].precio; // Revert to old value
      }
    },

    updateQuantity(index, event) {
      const newQuantity = parseInt(event.target.value, 10);
      if (newQuantity > 0) {
        this.cart[index].quantity = newQuantity;
      } else {
        // Remove the item if the quantity is set to 0 or less
        this.cart.splice(index, 1);
      }
    },
    
    closeCart() {
      this.showCart = false;
    },
    
    openCart() {
      this.showCart = true;
    },
    
    toggleSelectAll(event) {
      if (event.target.checked) {
        this.selectedItems = this.cart.map(item => item.id);
      } else {
        this.selectedItems = [];
      }
    },
    
    removerSeleccionados() {
      this.cart = this.cart.filter(item => !this.selectedItems.includes(item.id));
      this.selectedItems = [];
    },
    
    procesarCart() {
      if (!this.cart.length) return;
      
      // Validate nombres field
      if (!this.nombres.trim()) {
        alert('El campo Nombres es obligatorio.');
        return;
      }
      
      // Validate product quantities against available stock
      const stockValidation = this.validateStockQuantities();
      
      if (!stockValidation.isValid) {
        alert(`Error: ${stockValidation.message}`);
        return;
      }
      
      // Calculate total amount
      const montoTotal = this.cart.reduce((total, item) => total + (item.quantity * item.precio), 0);
      const productoComprobantePayload = {
        nombres: this.nombres,
        dni_ce: this.dni_ce,
        telefono: this.telefono,
        monto_total: parseFloat(montoTotal.toFixed(2)),
        items: this.cart.map(item => ({
          stock_id: item.id,
          cantidad: item.quantity,
          precio: item.precio
        }))
      };
      
      axios.post('/api/productos-comprobante', productoComprobantePayload)
        .then(response => {
          // Update stock quantities after successful submission
          this.updateStockQuantities();
          alert('Producto comprobante y items creados correctamente');
          
          // Reset form fields
          this.nombres = '';
          this.dni_ce = '';
          this.telefono = '';
          this.dniCeError = '';
          
          this.cart = [];
          this.selectedItems = [];
          this.closeCart();
          
          // Refresh items list to show updated stock quantities
          this.fetchItems();
        })
        .catch(error => {
          console.error('Error al crear producto comprobante/items:', error);
          alert('Error al crear producto comprobante/items');
        });
    },
    
    validateStockQuantities() {
      // Check if any product quantity exceeds available stock
      for (const item of this.cart) {
        if (item.tipo_producto !== 'u' && item.quantity > item.num_stock) {
          return {
            isValid: false,
            message: `El producto "${item.descripcion}" excede el stock disponible. Stock actual: ${item.num_stock}, Cantidad solicitada: ${item.quantity}`
          };
        }
      }
      return { isValid: true };
    },
    updateStockQuantities() {
      // Create an array of products that need stock updates
      const stockUpdates = this.cart.filter(item => item.tipo_producto !== 'u').map(item => ({
        id: item.id,
        num_stock: item.num_stock - item.quantity
      }));

      console.log('Updating stock for items:', this.cart);
      console.log('Stock updates to be sent:', stockUpdates);

      // Send request to update stock quantities
      axios.post('/api/update-stock', { updates: stockUpdates })
        .then(response => {
          console.log('Stock update response:', response.data);

          // Update local product list with new stock values
          stockUpdates.forEach(update => {
            const producto = this.items.find(p => p.id === update.id);
            if (producto) {
              producto.num_stock = update.num_stock;
            }
          });
        })
        .catch(error => {
          console.error('Error al actualizar el stock:', error);
        });
    },
    selectAllTipoProducto() {
      this.filters.tipo_producto = [];
      this.applyFilters();
      this.showTipoProductoDropdown = false;
    },
    toggleTipoProducto(tipo) {
      const idx = this.filters.tipo_producto.indexOf(tipo);
      if (idx === -1) {
        this.filters.tipo_producto.push(tipo);
      } else {
        this.filters.tipo_producto.splice(idx, 1);
      }
      // If all are unchecked, revert to 'Todos'
      if (this.filters.tipo_producto.length === 0) {
        this.applyFilters();
        this.showTipoProductoDropdown = false;
        return;
      }
      this.applyFilters();
    },
    openCart() {
      this.showCart = true;
    },
    closeCart() {
      this.showCart = false;
    },
    onFileChange() {
      this.importResult = null;
      this.alertMessage = '';
    },
    async importStock() {
      if (!this.$refs.importFile.files.length) return;
      this.importing = true;
      this.importResult = null;
      const formData = new FormData();
      formData.append('file', this.$refs.importFile.files[0]);
      try {
        const res = await axios.post('/api/stock/import', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        this.importResult = res.data;
        this.alertMessage = res.data.message || 'Importación completada.';
        this.fetchItems();
      } catch (err) {
        if (err.response && err.response.data) {
          this.importResult = err.response.data;
          this.alertMessage = err.response.data.message || 'Error al importar.';
        } else {
          this.importResult = { errors: [err.message] };
          this.alertMessage = 'Error al importar: ' + err.message;
        }
      } finally {
        this.importing = false;
        setTimeout(() => { this.alertMessage = ''; }, 7000);
      }
    },
    downloadFormat() {
      const csvContent = `codigo,descripcion,precio,num_stock,tipo_producto,genero,marca,material,fecha_compra,id_tipo_luna,id_diseno_luna,id_laboratorio_luna,indice\n`;

      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      const now = new Date();
      const pad = n => n.toString().padStart(2, '0');
      const fileName = `formato_stock_${now.getFullYear()}_${pad(now.getMonth() + 1)}_${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
      link.setAttribute('download', fileName);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    
    validateDniCe() {
      if (this.dni_ce && !/^[0-9]{8,9}$/.test(this.dni_ce)) {
        this.dniCeError = 'El DNI/CE debe contener entre 8 y 9 dígitos';
      } else {
        this.dniCeError = '';
      }
    },
  },
  mounted() {
    this.fetchItems();
    this.fetchMarcas();
    this.fetchMateriales();
    this.fetchProveedores();
    this.fetchTiposLuna();
    this.fetchDisenosLuna();
    this.fetchLaboratoriosLuna();
    
    // Close proveedor dropdown when clicking outside
    document.addEventListener('click', (e) => {
      const tipoDropdown = document.querySelector('.select-wrapper .dropdown-menu');
      const tipoControl = document.querySelector('.select-wrapper .form-control');
      if (this.showTipoProductoDropdown && tipoDropdown && !tipoDropdown.contains(e.target) && tipoControl && !tipoControl.contains(e.target)) {
        this.showTipoProductoDropdown = false;
      }
      const isClickInsideResults = e.target.closest('.proveedor-results');
      const isClickInsideInput = e.target.closest('.position-relative input');
      
      if (!isClickInsideResults && !isClickInsideInput) {
        this.showProveedorResults = false;
      }
    });
  },
  beforeUnmount() {
    document.removeEventListener('click', () => {});
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

.details-image {
  object-fit: contain;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 5px;
}

.no-image-placeholder {
  height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px dashed #ccc;
  color: #999;
  font-style: italic;
}

/* Proveedor selection styles */
.proveedor-selection {
  position: relative;
}

.selected-proveedores {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.selected-proveedor {
  display: inline-flex;
  align-items: center;
  font-size: 0.875rem;
}

.proveedor-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0 0 4px 4px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.proveedor-result-item {
  padding: 8px 12px;
  cursor: pointer;
}

.proveedor-result-item:hover,
.proveedor-result-item.active {
  background-color: #f8f9fa;
}

.proveedor-no-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0 0 4px 4px;
  padding: 8px 12px;
  color: #6c757d;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  z-index: 1000;
}

/* Ensure text doesn't overflow in selected proveedores badges */
.selected-proveedor .btn-close {
  width: 0.5em;
  height: 0.5em;
}

/* Cart related styles */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}

.btn-cart-icon {
  position: fixed;
  bottom: 80px;
  right: 20px;
  z-index: 1001;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: #007bff;
  color: #fff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  cursor: pointer;
}

.btn-cart-icon i {
  font-size: 1.8rem;
}

.cart-item-count {
  position: absolute;
  top: -10px;
  right: -10px;
  background-color: red;
  color: white;
  border-radius: 50%;
  padding: 0.2rem 0.5rem;
  font-size: 0.8rem;
}

.animate-add {
  animation: addItem 0.5s ease-in-out;
}

@keyframes addItem {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

.checkmark-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 128, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 3rem;
  animation: fadeOut 1.5s forwards;
}

.added-text {
  font-size: 1.5rem;
  margin-top: 0.5rem;
}

@keyframes fadeOut {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

.actions-cell .btn {
  width: 38px; /* Adjust width as needed for consistency */
  height: 30px; /* Adjust height to match btn-sm or custom */
  padding: 0.25rem 0.5rem; /* Default btn-sm padding, adjust if needed */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1; /* Ensure icon is vertically centered if text is removed */
}

.actions-cell .btn i {
  font-size: 0.875rem; /* Standard FontAwesome size for btn-sm */
  margin: 0; /* Remove any default margins from icons */
}

.action-buttons-grid {
  display: grid;
  grid-template-columns: repeat(2, auto); /* Two columns, auto-sized based on button content */
  gap: 4px; /* Space between buttons */
  justify-content: start; /* Align grid to the start of the cell */
  /* max-width: 70px; */ /* Approx (32px * 2) + 4px gap */
}

.action-buttons-grid .btn {
  width: 30px;  /* Fixed width for buttons */
  height: 30px; /* Fixed height for buttons */
  padding: 0.25rem; /* Adjust padding to center icon */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1; /* Ensure icon is vertically centered */
}

.action-buttons-grid .btn i {
  font-size: 0.8rem; /* Slightly smaller icon if needed for 30x30 button */
  margin: 0;
}

.import-stock-box {
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
</style>
