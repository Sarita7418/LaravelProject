import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../lib/axios';
import Select from 'react-select';
import CrearProducto from './CrearProducto';
import CrearProveedor from './CrearProveedor';
import './CrearCompra.css';

const CrearCompra = () => {
    const navigate = useNavigate();
    
    const [proveedores, setProveedores] = useState([]);
    const [productos, setProductos] = useState([]);
    const [categorias, setCategorias] = useState([]);
    const [unidades, setUnidades] = useState([]);
    const [sucursalesProveedor, setSucursalesProveedor] = useState([]);
    
    const [isModalProdOpen, setIsModalProdOpen] = useState(false);
    const [isModalProvOpen, setIsModalProvOpen] = useState(false);
    
    const [loading, setLoading] = useState(false);
    const [cargandoDatos, setCargandoDatos] = useState(true);
    const [guardandoProducto, setGuardandoProducto] = useState(false);
    const [productoActual, setProductoActual] = useState({
        id_producto: '',
        nombre: '',
        cantidad: 1,
        precio_unitario: 0,
        descuento_pct: 0,
        descuento_monto: 0,
        subtotal: 0,
        codigo_barras: '',
        numero_lote: '',
        fecha_vencimiento: ''
    });

    const [formData, setFormData] = useState({
        id_proveedor: '',
        id_sucursal_proveedor: '',
        fecha_compra: new Date().toISOString().split('T')[0],
        nro_documento: '',
        fecha_limite_emision: '',
        observacion: ''
    });

    const [detalle, setDetalle] = useState([]);
    const [descuentoHabilitado, setDescuentoHabilitado] = useState(false);

    const limpiarTexto = (texto) => {
        if (!texto && texto !== 0) return '';
        return texto.toString().replace(/['"]/g, '').trim();
    };

    useEffect(() => {
        cargarDatosIniciales();
    }, []);

    useEffect(() => {
        if (formData.id_proveedor) {
            cargarSucursalesProveedor(formData.id_proveedor);
        } else {
            setSucursalesProveedor([]);
            setFormData(prev => ({ ...prev, id_sucursal_proveedor: '' }));
        }
    }, [formData.id_proveedor]);

    const calcularSubtotal = (cantidad, precio, descuentoPct, descuentoMonto) => {
        const cant = parseFloat(cantidad) || 0;
        const precioUnit = parseFloat(precio) || 0;
        const totalSinDescuento = cant * precioUnit;
        
        let descuento = 0;
        if (descuentoPct > 0) {
            descuento = (totalSinDescuento * descuentoPct) / 100;
        } else if (descuentoMonto > 0) {
            descuento = descuentoMonto;
        }
        
        if (descuento > totalSinDescuento) {
            descuento = totalSinDescuento;
        }
        
        return {
            totalSinDescuento: totalSinDescuento,
            descuentoMonto: descuento,
            subtotal: totalSinDescuento - descuento
        };
    };

    useEffect(() => {
        const { subtotal } = calcularSubtotal(
            productoActual.cantidad,
            productoActual.precio_unitario,
            productoActual.descuento_pct,
            productoActual.descuento_monto
        );
        
        setProductoActual(prev => ({
            ...prev,
            subtotal: subtotal
        }));
    }, [productoActual.cantidad, productoActual.precio_unitario, productoActual.descuento_pct, productoActual.descuento_monto]);

    const cargarDatosIniciales = async () => {
        try {
            setCargandoDatos(true);
            
            try {
                const resProv = await axios.get('/api/proveedores/select');
                setProveedores(resProv.data?.data || []);
            } catch (error) {
                console.error('Error cargando proveedores:', error);
                setProveedores([]);
            }

            try {
                const resProd = await axios.get('/api/productos');
                setProductos(resProd.data?.data || []);
            } catch (error) {
                console.error('Error cargando productos:', error);
                setProductos([]);
            }

            try {
                const resCat = await axios.get('/api/productos/categorias');
                setCategorias(resCat.data?.data || []);
            } catch (error) {
                console.error('Error cargando categorías:', error);
                setCategorias([]);
            }

            try {
                const resUni = await axios.get('/api/productos/unidades');
                setUnidades(resUni.data?.data || []);
            } catch (error) {
                console.error('Error cargando unidades:', error);
                setUnidades([]);
            }

            try {
                const resNum = await axios.get('/api/compras/generar-numero');
                if (resNum.data?.success && resNum.data.data) {
                    setFormData(prev => ({
                        ...prev,
                        nro_documento: limpiarTexto(resNum.data.data)
                    }));
                }
            } catch (error) {
                console.error('Error generando número:', error);
                const numeroLocal = `CMP-${new Date().getFullYear()}-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`;
                setFormData(prev => ({
                    ...prev,
                    nro_documento: limpiarTexto(numeroLocal)
                }));
            }
            
        } catch (e) {
            console.error("Error al cargar datos iniciales:", e);
            alert("Error al cargar algunos datos iniciales");
        } finally {
            setCargandoDatos(false);
        }
    };

    const cargarSucursalesProveedor = async (idProveedor) => {
        try {
            const response = await axios.get('/api/proveedores/sucursales', {
                params: { id_proveedor: idProveedor }
            });
            
            const sucursalesData = response.data?.data || [];
            setSucursalesProveedor(sucursalesData);
            
            if (sucursalesData.length === 1) {
                setFormData(prev => ({ 
                    ...prev, 
                    id_sucursal_proveedor: sucursalesData[0].value 
                }));
            }
        } catch (error) {
            console.error("Error cargando sucursales:", error);
            setSucursalesProveedor([]);
        }
    };

    const seleccionarProducto = (seleccion) => {
        if (!seleccion || !seleccion.value) return;
        
        const producto = productos.find(prod => prod.id === seleccion.value);
        
        if (!producto) {
            alert("Producto no encontrado");
            return;
        }

        const productoExistente = detalle.find(item => item.id_producto === producto.id);
        
        if (productoExistente) {
            setProductoActual({
                id_producto: productoExistente.id_producto,
                nombre: productoExistente.nombre,
                cantidad: productoExistente.cantidad,
                precio_unitario: productoExistente.precio_unitario,
                descuento_pct: productoExistente.descuento_pct || 0,
                descuento_monto: productoExistente.descuento_monto || 0,
                subtotal: productoExistente.subtotal,
                codigo_barras: productoExistente.codigo_barras || '',
                numero_lote: productoExistente.numero_lote || '',
                fecha_vencimiento: productoExistente.fecha_vencimiento || ''
            });
        } else {
            setProductoActual({
                id_producto: producto.id,
                nombre: producto.nombre || `Producto ${producto.id}`,
                cantidad: 1,
                precio_unitario: parseFloat(producto.precio_entrada) || 0,
                descuento_pct: 0,
                descuento_monto: 0,
                subtotal: parseFloat(producto.precio_entrada) || 0,
                codigo_barras: producto.codigo_barras || '',
                numero_lote: '',
                fecha_vencimiento: ''
            });
        }
        
        setDescuentoHabilitado(false);
    };

    const calcularDescuentoDesdeMonto = () => {
        const cantidad = parseFloat(productoActual.cantidad) || 0;
        const precio = parseFloat(productoActual.precio_unitario) || 0;
        const totalSinDescuento = cantidad * precio;
        
        if (totalSinDescuento > 0 && productoActual.descuento_monto > 0) {
            const porcentaje = (productoActual.descuento_monto / totalSinDescuento) * 100;
            setProductoActual(prev => ({
                ...prev,
                descuento_pct: porcentaje
            }));
        }
    };

    const calcularDescuentoDesdePorcentaje = () => {
        const cantidad = parseFloat(productoActual.cantidad) || 0;
        const precio = parseFloat(productoActual.precio_unitario) || 0;
        const totalSinDescuento = cantidad * precio;
        
        if (productoActual.descuento_pct > 0) {
            const monto = (totalSinDescuento * productoActual.descuento_pct) / 100;
            setProductoActual(prev => ({
                ...prev,
                descuento_monto: monto
            }));
        }
    };

    const guardarProductoEnDetalle = () => {
        if (!productoActual.id_producto) {
            alert("Seleccione un producto primero");
            return;
        }

        if (!productoActual.cantidad || productoActual.cantidad <= 0) {
            alert("La cantidad debe ser mayor a 0");
            return;
        }

        if (!productoActual.precio_unitario || productoActual.precio_unitario <= 0) {
            alert("El precio unitario debe ser mayor a 0");
            return;
        }

        if (productoActual.descuento_pct > 100) {
            alert("El descuento no puede ser mayor al 100%");
            return;
        }

        if (productoActual.descuento_monto < 0) {
            alert("El descuento no puede ser negativo");
            return;
        }

        const cantidad = parseFloat(productoActual.cantidad);
        const precioUnitario = parseFloat(productoActual.precio_unitario);
        const descuentoPct = parseFloat(productoActual.descuento_pct) || 0;
        const descuentoMonto = parseFloat(productoActual.descuento_monto) || 0;
        
        const { totalSinDescuento, descuentoMonto: descuentoCalculado, subtotal } = calcularSubtotal(
            cantidad,
            precioUnitario,
            descuentoPct,
            descuentoMonto
        );

        if (descuentoCalculado > totalSinDescuento) {
            alert("El descuento no puede ser mayor al total");
            return;
        }

        const indexExistente = detalle.findIndex(item => item.id_producto === productoActual.id_producto);
        
        if (indexExistente >= 0) {
            const nuevoDetalle = [...detalle];
            nuevoDetalle[indexExistente] = {
                id_producto: productoActual.id_producto,
                nombre: productoActual.nombre,
                cantidad: cantidad,
                precio_unitario: precioUnitario,
                descuento_pct: descuentoPct,
                descuento_monto: descuentoCalculado,
                subtotal: subtotal,
                codigo_barras: productoActual.codigo_barras || '',
                numero_lote: productoActual.numero_lote || '',
                fecha_vencimiento: productoActual.fecha_vencimiento || ''
            };
            setDetalle(nuevoDetalle);
        } else {
            const nuevoProducto = {
                id_producto: productoActual.id_producto,
                nombre: productoActual.nombre,
                cantidad: cantidad,
                precio_unitario: precioUnitario,
                descuento_pct: descuentoPct,
                descuento_monto: descuentoCalculado,
                subtotal: subtotal,
                codigo_barras: productoActual.codigo_barras || '',
                numero_lote: productoActual.numero_lote || '',
                fecha_vencimiento: productoActual.fecha_vencimiento || ''
            };
            setDetalle(prev => [...prev, nuevoProducto]);
        }

        setProductoActual({
            id_producto: '',
            nombre: '',
            cantidad: 1,
            precio_unitario: 0,
            descuento_pct: 0,
            descuento_monto: 0,
            subtotal: 0,
            codigo_barras: '',
            numero_lote: '',
            fecha_vencimiento: ''
        });
        
        setDescuentoHabilitado(false);
    };

    const manejarProductoCreado = (respuesta) => {
        if (!respuesta || !respuesta.success) return;
        
        cargarDatosIniciales();
        
        if (respuesta.data) {
            const producto = respuesta.data;
            setProductoActual({
                id_producto: producto.id,
                nombre: producto.nombre || `Producto ${producto.id}`,
                cantidad: 1,
                precio_unitario: parseFloat(producto.precio_entrada) || 0,
                descuento_pct: 0,
                descuento_monto: 0,
                subtotal: parseFloat(producto.precio_entrada) || 0,
                codigo_barras: '',
                numero_lote: '',
                fecha_vencimiento: ''
            });
        }
    };

    const manejarProveedorCreado = (respuesta) => {
        if (!respuesta || !respuesta.success) return;
        
        cargarDatosIniciales();
        
        if (respuesta.data?.empresa?.id) {
            setFormData(prev => ({ 
                ...prev, 
                id_proveedor: respuesta.data.empresa.id 
            }));
        }
    };

    const eliminarProductoDelDetalle = (idProducto) => {
        setDetalle(prev => prev.filter(item => item.id_producto !== idProducto));
    };

    const calcularTotal = () => {
        return detalle.reduce((acc, item) => {
            return acc + (parseFloat(item.subtotal) || 0);
        }, 0);
    };

    const calcularTotalDescuentos = () => {
        return detalle.reduce((acc, item) => {
            return acc + (parseFloat(item.descuento_monto) || 0);
        }, 0);
    };

    const calcularTotalSinDescuentos = () => {
        return detalle.reduce((acc, item) => {
            const cantidad = parseFloat(item.cantidad) || 0;
            const precio = parseFloat(item.precio_unitario) || 0;
            return acc + (cantidad * precio);
        }, 0);
    };

    const validarFormulario = () => {
        if (!formData.id_proveedor) {
            alert("Seleccione un proveedor");
            return false;
        }
        
        if (detalle.length === 0) {
            alert("Agregue productos a la compra");
            return false;
        }

        for (const item of detalle) {
            if (!item.cantidad || item.cantidad <= 0) {
                alert(`La cantidad del producto "${item.nombre}" es inválida`);
                return false;
            }
            if (!item.precio_unitario || item.precio_unitario <= 0) {
                alert(`El precio del producto "${item.nombre}" es inválido`);
                return false;
            }
        }

        return true;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validarFormulario()) return;

        setLoading(true);
        try {
            const payload = {
                id_proveedor: formData.id_proveedor,
                id_sucursal_proveedor: formData.id_sucursal_proveedor || null,
                fecha_compra: formData.fecha_compra,
                nro_documento: limpiarTexto(formData.nro_documento),
                fecha_limite_emision: formData.fecha_limite_emision || null,
                observacion: limpiarTexto(formData.observacion) || null,
                detalles: detalle.map(item => ({
                    id_producto: item.id_producto,
                    cantidad: parseFloat(item.cantidad),
                    precio_unitario: parseFloat(item.precio_unitario),
                    descuento_pct: parseFloat(item.descuento_pct) || 0,
                    descuento_monto: parseFloat(item.descuento_monto) || 0,
                    codigo_barras: limpiarTexto(item.codigo_barras) || '',
                    numero_lote: limpiarTexto(item.numero_lote) || '',
                    fecha_vencimiento: item.fecha_vencimiento || null
                }))
            };
            
            console.log('Payload enviado:', JSON.stringify(payload, null, 2));
            
            const response = await axios.post('/api/compras', payload);
            
            if (response.data?.success) {
                alert(response.data.message || 'Compra registrada exitosamente');
                navigate('/dashboard/compras');
            } else {
                alert(response.data?.message || "Error al registrar la compra");
            }
        } catch (error) {
            console.error('Error detallado:', error);
            
            let errorMessage = "Error al registrar la compra";
            
            if (error.response) {
                console.error('Respuesta del servidor:', error.response.data);
                
                if (error.response.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response.data?.error) {
                    errorMessage = error.response.data.error;
                }
                
                if (error.response.status === 422 && error.response.data.errors) {
                    const validationErrors = error.response.data.errors;
                    const errorList = Object.values(validationErrors).flat().join('\n');
                    errorMessage = `Errores de validación:\n${errorList}`;
                }
            } else if (error.request) {
                console.error('No hubo respuesta:', error.request);
                errorMessage = "No se pudo conectar con el servidor. Verifica tu conexión a internet.";
            } else {
                console.error('Error de configuración:', error.message);
            }
            
            alert(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    const productosOptions = productos.map(p => ({
        value: p.id,
        label: `${p.nombre} - Bs. ${p.precio_entrada || 0}`
    }));

    if (cargandoDatos) {
        return (
            <div className="crear-compra-container">
                <div className="loading">Cargando datos...</div>
            </div>
        );
    }

    return (
        <div className="crear-compra-container">
            <div className="crear-compra-header">
                <h1 className="compras-title">Registrar Nueva Compra</h1>
                <button type="button" className="btn-secundario" onClick={() => navigate('/dashboard/compras')}>
                    Cancelar y Volver
                </button>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="form-section">
                    <h3>Información de la Compra</h3>
                    <div className="form-grid">
                        <div className="form-group">
                            <label>Proveedor (Empresa) *</label>
                            <div className="proveedor-selector">
                                <div className="select-container">
                                    <Select 
                                        options={proveedores} 
                                        placeholder="Seleccionar proveedor..."
                                        value={proveedores.find(p => p.value == formData.id_proveedor)}
                                        onChange={s => {
                                            setFormData({
                                                ...formData, 
                                                id_proveedor: s?.value || '',
                                                id_sucursal_proveedor: ''
                                            });
                                        }}
                                        isSearchable
                                        required
                                        menuPortalTarget={document.body}
                                        menuPosition="fixed"
                                        menuShouldScrollIntoView={false}
                                    />
                                </div>
                                <button 
                                    type="button" 
                                    className="btn-primary btn-nuevo-proveedor"
                                    onClick={() => setIsModalProvOpen(true)}
                                    title="Nuevo Proveedor"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                        
                        <div className="form-group">
                            <label>Sucursal del Proveedor</label>
                            <Select 
                                options={sucursalesProveedor} 
                                placeholder="Seleccionar sucursal..."
                                value={sucursalesProveedor.find(s => s.value == formData.id_sucursal_proveedor)}
                                onChange={s => setFormData({...formData, id_sucursal_proveedor: s?.value || ''})}
                                isSearchable
                                isDisabled={!formData.id_proveedor || sucursalesProveedor.length === 0}
                                menuPortalTarget={document.body}
                                menuPosition="fixed"
                                menuShouldScrollIntoView={false}
                            />
                            {sucursalesProveedor.length === 0 && formData.id_proveedor && (
                                <small className="text-muted">
                                    Este proveedor no tiene sucursales registradas
                                </small>
                            )}
                        </div>
                        
                        <div className="form-group">
                            <label>Fecha *</label>
                            <input 
                                type="date" 
                                className="form-input" 
                                value={formData.fecha_compra}
                                onChange={e => setFormData({...formData, fecha_compra: e.target.value})}
                                required
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>N° Documento Interno</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                placeholder="Número de documento interno"
                                value={formData.nro_documento}
                                onChange={e => setFormData({
                                    ...formData, 
                                    nro_documento: limpiarTexto(e.target.value)
                                })}
                                readOnly
                            />
                        </div>
                    </div>

                    <div className="form-grid">                                             
                        <div className="form-group">
                            <label>Fecha Límite Emisión</label>
                            <input 
                                type="date" 
                                className="form-input" 
                                value={formData.fecha_limite_emision}
                                onChange={e => setFormData({...formData, fecha_limite_emision: e.target.value})}
                            />
                        </div>
                    </div>
                    
                    <div className="form-group">
                        <label>Observaciones</label>
                        <textarea 
                            className="form-input" 
                            placeholder="Observaciones de la compra..."
                            value={formData.observacion}
                            onChange={e => setFormData({
                                ...formData, 
                                observacion: limpiarTexto(e.target.value)
                            })}
                            rows="3"
                        />
                    </div>
                </div>

                <div className="form-section">
                    <h3>Productos de la Compra</h3>
                    
                    <div className="buscar-producto-container">
                        <div className="buscar-producto-input">
                            <label>Buscar Producto</label>
                            <Select 
                                options={productosOptions} 
                                placeholder="Escriba el nombre del producto..."
                                onChange={seleccionarProducto}
                                value={null}
                                isSearchable
                            />
                        </div>
                        <button 
                            type="button" 
                            className="btn-primary btn-nuevo-producto"
                            onClick={() => setIsModalProdOpen(true)}
                        >
                            + Nuevo Producto
                        </button>
                    </div>

                    {productoActual.id_producto && (
                        <div className="agregar-producto-form">
                            <h4>Agregar/Editar Producto</h4>
                            <div className="form-grid-3">
                                <div className="form-group">
                                    <label>Producto</label>
                                    <input 
                                        type="text" 
                                        className="form-input" 
                                        value={productoActual.nombre}
                                        readOnly
                                    />
                                </div>
                                <div className="form-group">
                                    <label>Cantidad</label>
                                    <input 
                                        type="number" 
                                        className="form-input cantidad-input"
                                        value={productoActual.cantidad} 
                                        min="1"
                                        step="1"
                                        onChange={e => setProductoActual({
                                            ...productoActual,
                                            cantidad: e.target.value
                                        })}
                                        onBlur={e => {
                                            if (e.target.value < 1) {
                                                e.target.value = 1;
                                                setProductoActual({
                                                    ...productoActual,
                                                    cantidad: 1
                                                });
                                            }
                                        }}
                                    />
                                </div>
                                <div className="form-group">
                                    <label>Costo Unitario (Bs)</label>
                                    <input 
                                        type="number" 
                                        step="0.00" 
                                        min="0.01"
                                        className="form-input precio-input"
                                        value={productoActual.precio_unitario}
                                        onChange={e => setProductoActual({
                                            ...productoActual,
                                            precio_unitario: e.target.value
                                        })}
                                        onBlur={e => {
                                            if (e.target.value < 0.01) {
                                                e.target.value = 0.01;
                                                setProductoActual({
                                                    ...productoActual,
                                                    precio_unitario: 0.01
                                                });
                                            }
                                        }}
                                    />
                                </div>
                            </div>
                            
                            <div className="form-grid-2">
                                <div className="form-group">
                                    <label>Código de Barras (Opcional)</label>
                                    <input 
                                        type="text" 
                                        className="form-input" 
                                        value={productoActual.codigo_barras}
                                        onChange={e => setProductoActual({...productoActual, codigo_barras: e.target.value})}
                                        placeholder="Ej: 123456789012"
                                    />
                                </div>
                                
                                <div className="form-group">
                                    <label>Número de Lote (Opcional)</label>
                                    <input 
                                        type="text" 
                                        className="form-input" 
                                        value={productoActual.numero_lote}
                                        onChange={e => setProductoActual({...productoActual, numero_lote: e.target.value})}
                                        placeholder="Ej: L-101, BATCH-2024"
                                    />
                                </div>
                            </div>
                            
                            <div className="form-grid-2">
                                <div className="form-group">
                                    <label>Fecha de Vencimiento (Opcional)</label>
                                    <input 
                                        type="date" 
                                        className="form-input" 
                                        value={productoActual.fecha_vencimiento}
                                        onChange={e => setProductoActual({...productoActual, fecha_vencimiento: e.target.value})}
                                    />
                                </div>
                                
                                <div className="form-group">
                                    <label>Subtotal</label>
                                    <input 
                                        type="text" 
                                        className="form-input subtotal-input"
                                        value={`Bs. ${productoActual.subtotal.toFixed(2)}`}
                                        readOnly
                                    />
                                </div>
                            </div>
                            
                            <div className="descuento-section">
                                <div className="descuento-header">
                                    <div className="checkbox-container">
                                        <input 
                                            type="checkbox" 
                                            id="aplicarDescuento"
                                            checked={descuentoHabilitado}
                                            onChange={(e) => setDescuentoHabilitado(e.target.checked)}
                                        />
                                        <label htmlFor="aplicarDescuento" className="descuento-label">
                                            Aplicar Descuento (Opcional)
                                        </label>
                                    </div>
                                </div>
                                
                                {descuentoHabilitado && (
                                    <div className="descuento-options">
                                        <div className="form-grid-2">
                                            <div className="form-group">
                                                <label>Descuento Porcentaje (%)</label>
                                                <input 
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    className="form-input"
                                                    value={productoActual.descuento_pct}
                                                    onChange={e => {
                                                        const valor = e.target.value;
                                                        setProductoActual({
                                                            ...productoActual,
                                                            descuento_pct: valor
                                                        });
                                                    }}
                                                    onBlur={() => {
                                                        if (productoActual.descuento_pct > 100) {
                                                            alert("El descuento no puede ser mayor al 100%");
                                                            setProductoActual(prev => ({
                                                                ...prev,
                                                                descuento_pct: 100
                                                            }));
                                                        }
                                                        calcularDescuentoDesdePorcentaje();
                                                    }}
                                                />
                                            </div>
                                            
                                            <div className="form-group">
                                                <label>Descuento Monto (Bs)</label>
                                                <input 
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    className="form-input"
                                                    value={productoActual.descuento_monto}
                                                    onChange={e => {
                                                        const valor = e.target.value;
                                                        setProductoActual({
                                                            ...productoActual,
                                                            descuento_monto: valor
                                                        });
                                                    }}
                                                    onBlur={calcularDescuentoDesdeMonto}
                                                />
                                            </div>
                                        </div>
                                        
                                        <div className="descuento-info">
                                            <div className="descuento-info-item">
                                                <span>Total sin descuento:</span>
                                                <span>Bs. {(parseFloat(productoActual.cantidad) * parseFloat(productoActual.precio_unitario)).toFixed(2)}</span>
                                            </div>
                                            <div className="descuento-info-item">
                                                <span>Descuento aplicado:</span>
                                                <span className="descuento-monto">- Bs. {parseFloat(productoActual.descuento_monto).toFixed(2)} ({parseFloat(productoActual.descuento_pct).toFixed(2)}%)</span>
                                            </div>
                                            <div className="descuento-info-item total">
                                                <span>Total con descuento:</span>
                                                <span className="total-final">Bs. {productoActual.subtotal.toFixed(2)}</span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                            
                            <div className="form-group btn-agregar-container">
                                <button 
                                    type="button" 
                                    className="btn-primary btn-agregar"
                                    onClick={guardarProductoEnDetalle}
                                    disabled={guardandoProducto}
                                >
                                    {guardandoProducto ? 'Guardando...' : 'Aceptar y Agregar'}
                                </button>
                            </div>
                        </div>
                    )}

                    {detalle.length > 0 ? (
                        <>
                            <h4>Productos Agregados a la Compra</h4>
                            <table className="productos-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Costo Unit. (Bs)</th>
                                        <th>Descuento</th>
                                        <th>Subtotal (Bs)</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {detalle.map((item, index) => (
                                        <tr key={`${item.id_producto}-${index}`}>
                                            <td>{item.nombre}</td>
                                            <td className="text-center">{item.cantidad}</td>
                                            <td className="text-right">Bs. {parseFloat(item.precio_unitario).toFixed(2)}</td>
                                            <td className="text-right">
                                                {item.descuento_monto > 0 ? (
                                                    <div className="descuento-celda">
                                                        <div className="descuento-monto">Bs. {parseFloat(item.descuento_monto).toFixed(2)}</div>
                                                        <div className="descuento-porcentaje">({parseFloat(item.descuento_pct).toFixed(2)}%)</div>
                                                    </div>
                                                ) : (
                                                    <span className="sin-descuento">Sin descuento</span>
                                                )}
                                            </td>
                                            <td className="text-right">Bs. {parseFloat(item.subtotal).toFixed(2)}</td>
                                            <td className="text-center">
                                                <button 
                                                    type="button" 
                                                    className="btn-editar"
                                                    onClick={() => seleccionarProducto({ value: item.id_producto })}
                                                    title="Editar"
                                                >
                                                Editar
                                                </button>
                                                <button 
                                                    type="button" 
                                                    className="btn-anular btn-eliminar-fila"
                                                    onClick={() => eliminarProductoDelDetalle(item.id_producto)}
                                                    title="Eliminar"
                                                >
                                                Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>

                            <div className="totales-section">
                                <div className="total-item">
                                    <span>Total sin descuentos:</span>
                                    <span>Bs. {calcularTotalSinDescuentos().toFixed(2)}</span>
                                </div>
                                <div className="total-item">
                                    <span>Total descuentos:</span>
                                    <span className="descuento-total">- Bs. {calcularTotalDescuentos().toFixed(2)}</span>
                                </div>
                                <div className="total-item total-final">
                                    <span>TOTAL COMPRA:</span>
                                    <span className="total-monto">
                                        Bs. {calcularTotal().toFixed(2)}
                                    </span>
                                </div>
                            </div>
                        </>
                    ) : (
                        <div className="sin-productos">
                            No hay productos en la compra. Agregue productos usando el buscador o creando uno nuevo.
                        </div>
                    )}
                </div>

                <div className="form-actions">
                    <button 
                        type="button" 
                        className="btn-cancelar" 
                        onClick={() => navigate('/dashboard/compras')}
                        disabled={loading}
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        className="btn-guardar" 
                        disabled={loading || detalle.length === 0 || !formData.id_proveedor}
                    >
                        {loading ? 'Guardando...' : 'Confirmar y Guardar Compra'}
                    </button>
                </div>
            </form>

            <CrearProducto 
                isOpen={isModalProdOpen} 
                onClose={() => setIsModalProdOpen(false)} 
                onProductoCreado={manejarProductoCreado} 
                categorias={categorias}
                unidades={unidades}
            />

            <CrearProveedor 
                isOpen={isModalProvOpen} 
                onClose={() => setIsModalProvOpen(false)} 
                onProveedorCreado={manejarProveedorCreado} 
            />
        </div>
    );
};

export default CrearCompra;