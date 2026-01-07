import React, { useState } from 'react';
import axios from '../../lib/axios';
import './CrearProveedor.css';

const CrearProveedor = ({ isOpen, onClose, onProveedorCreado }) => {
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        razon_social: '',
        nombre_comercial: '',
        nit: '',
        telefono: '',
        email: '',
        direccion_fiscal: '',
        municipio: '',
        departamento: '',
        matricula_comercio: ''
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!formData.razon_social || !formData.nit) {
            alert('Razón Social y NIT son obligatorios');
            return;
        }

        setLoading(true);
        try {
            const res = await axios.post('/api/proveedores/crear-desde-compra', formData);
            
            if (res.data?.success) {
                onProveedorCreado(res.data);
                alert(res.data.message || 'Proveedor creado exitosamente');
                onClose();
                
                setFormData({
                    razon_social: '',
                    nombre_comercial: '',
                    nit: '',
                    telefono: '',
                    email: '',
                    direccion_fiscal: '',
                    municipio: '',
                    departamento: '',
                    matricula_comercio: ''
                });
            } else {
                alert(res.data?.message || "Error al crear el proveedor");
            }
        } catch (err) {
            console.error('Error al crear proveedor:', err);
            alert(err.response?.data?.message || "Error al crear el proveedor");
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="modal-overlay">
            <div className="modal-content">
                <h2 className="compras-title">Registrar Nuevo Proveedor (Empresa)</h2>
                <form onSubmit={handleSubmit}>
                    <div className="form-grid">
                        <div className="form-group">
                            <label>Razón Social *</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                required 
                                value={formData.razon_social}
                                onChange={e => setFormData({...formData, razon_social: e.target.value})} 
                                placeholder="Ej: Distribuidora Andina S.A."
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>Nombre Comercial</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                value={formData.nombre_comercial}
                                onChange={e => setFormData({...formData, nombre_comercial: e.target.value})}
                                placeholder="Nombre de marca"
                            />
                        </div>
                    </div>
                    
                    <div className="form-grid">
                        <div className="form-group">
                            <label>NIT *</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                required
                                value={formData.nit}
                                onChange={e => setFormData({...formData, nit: e.target.value})}
                                placeholder="Ej: 123456789"
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>Matrícula Comercio</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                value={formData.matricula_comercio}
                                onChange={e => setFormData({...formData, matricula_comercio: e.target.value})}
                                placeholder="Número de matrícula"
                            />
                        </div>
                    </div>
                    
                    <div className="form-grid">
                        <div className="form-group">
                            <label>Teléfono</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                value={formData.telefono}
                                onChange={e => setFormData({...formData, telefono: e.target.value})}
                                placeholder="Ej: 77777777"
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>Email</label>
                            <input 
                                type="email" 
                                className="form-input" 
                                value={formData.email}
                                onChange={e => setFormData({...formData, email: e.target.value})}
                                placeholder="contacto@empresa.com"
                            />
                        </div>
                    </div>
                    
                    <div className="form-grid">
                        <div className="form-group">
                            <label>Municipio</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                value={formData.municipio}
                                onChange={e => setFormData({...formData, municipio: e.target.value})}
                                placeholder="Municipio"
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>Departamento</label>
                            <input 
                                type="text" 
                                className="form-input" 
                                value={formData.departamento}
                                onChange={e => setFormData({...formData, departamento: e.target.value})}
                                placeholder="Departamento"
                            />
                        </div>
                    </div>
                    
                    <div className="form-group">
                        <label>Dirección Fiscal *</label>
                        <textarea 
                            className="form-input" 
                            value={formData.direccion_fiscal}
                            onChange={e => setFormData({...formData, direccion_fiscal: e.target.value})}
                            placeholder="Dirección completa..."
                            rows="3"
                            required
                        />
                    </div>
                    
                    <div className="form-actions">
                        <button type="button" className="btn-cancelar" onClick={onClose} disabled={loading}>
                            Cancelar
                        </button>
                        <button type="submit" className="btn-guardar" disabled={loading}>
                            {loading ? 'Guardando...' : 'Registrar Proveedor'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CrearProveedor;