import React, { useState, useEffect } from 'react';
import axios from 'axios';
import "./Ventas.css";

const Ventas = () => {
  const [nit, setNit] = useState('');
  const [razonSocial, setRazonSocial] = useState('');

  const [productos, setProductos] = useState([]);
  const [productoSeleccionado, setProductoSeleccionado] = useState('');
  const [cantidad, setCantidad] = useState(1);

  const [carrito, setCarrito] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(false);

  // --- NUEVOS ESTADOS PARA VALIDACIÓN VISUAL ---
  const [infoStock, setInfoStock] = useState({
    maximo: 0,
    disponible: 0,
    mensaje: '',
    esValido: true
  });

  useEffect(() => {
    cargarProductos();
  }, []);

  const cargarProductos = async () => {
    try {
      const res = await axios.get('/api/productos');
      const activos = res.data.data.filter(p => p.id_estado_producto === 22);
      setProductos(activos || []);
    } catch (error) {
      console.error("Error cargando productos", error);
    }
  };

  // --- EFECTO MÁGICO: CALCULA STOCK EN TIEMPO REAL ---
  useEffect(() => {
    if (!productoSeleccionado) {
      setInfoStock({ maximo: 0, disponible: 0, mensaje: '', esValido: true });
      return;
    }

    const prodObj = productos.find(p => p.id == productoSeleccionado);
    if (!prodObj) return;

    // Calculamos cuánto ya tenemos en el carrito de este producto
    const enCarrito = carrito.find(item => item.producto_id == productoSeleccionado);
    const cantidadEnCarrito = enCarrito ? parseFloat(enCarrito.cantidad) : 0;

    // Stock REAL disponible para agregar (Total - Lo que ya tienes en la cesta)
    const stockRealDisponible = parseFloat(prodObj.stock_total) - cantidadEnCarrito;

    // Validamos contra lo que el usuario está escribiendo ahora
    const cantidadSolicitada = parseFloat(cantidad);
    const esValido = cantidadSolicitada <= stockRealDisponible && cantidadSolicitada > 0;

    let mensaje = '';
    if (cantidadSolicitada > stockRealDisponible) {
      if (stockRealDisponible === 0) {
        mensaje = '❌ Ya no queda stock disponible (revisa tu carrito)';
      } else {
        mensaje = `Cantidad seleccionada no disponible`;
      }
    } else {
      mensaje = `Producto disponible.`;
    }

    setInfoStock({
      maximo: stockRealDisponible,
      disponible: stockRealDisponible,
      mensaje: mensaje,
      esValido: esValido
    });

  }, [productoSeleccionado, cantidad, carrito, productos]);


  const agregarAlCarrito = () => {
    // Validación bloqueante (por si acaso habilitan el botón con hack)
    if (!productoSeleccionado || !infoStock.esValido || cantidad <= 0) {
      return;
    }

    const prodObj = productos.find(p => p.id == productoSeleccionado);
    const cantidadSolicitada = parseFloat(cantidad);

    const existe = carrito.find(item => item.producto_id == productoSeleccionado);

    if (existe) {
      const nuevaCantidadTotal = parseFloat(existe.cantidad) + cantidadSolicitada;

      const nuevoCarrito = carrito.map(item =>
        item.producto_id == productoSeleccionado
          ? {
            ...item,
            cantidad: nuevaCantidadTotal,
            subtotal: nuevaCantidadTotal * parseFloat(item.precio)
          }
          : item
      );
      setCarrito(nuevoCarrito);
    } else {
      setCarrito([
        ...carrito,
        {
          producto_id: prodObj.id,
          nombre: prodObj.nombre,
          precio: prodObj.precio_salida,
          cantidad: cantidadSolicitada,
          subtotal: cantidadSolicitada * parseFloat(prodObj.precio_salida)
        }
      ]);
    }

    // Resetear solo cantidad, dejar producto seleccionado para agregar mas rapido si quiere
    setCantidad(1);
    // setProductoSeleccionado(''); // Opcional: si quieres que se limpie, descomenta esto
  };

  useEffect(() => {
    setTotal(carrito.reduce((acc, item) => acc + item.subtotal, 0));
  }, [carrito]);

  const eliminarItem = (id) => {
    setCarrito(carrito.filter(item => item.producto_id !== id));
  };

  const procesarVenta = async () => {
    if (!nit.trim() || !razonSocial.trim()) {
      alert("Ingrese NIT y Razón Social");
      return;
    }
    if (carrito.length === 0) {
      alert("Agregue productos al carrito");
      return;
    }
    if (!confirm(`¿Confirmar venta por ${total.toFixed(2)} Bs?`)) return;

    setLoading(true);

    const payload = {
      nit,
      razon_social: razonSocial,
      detalles: carrito.map(item => ({
        producto_id: item.producto_id,
        cantidad: item.cantidad
      }))
    };

    try {
      const res = await axios.post('/api/facturas', payload);
      alert(`✅ Venta Exitosa! Factura #${res.data.factura_id}`);
      setCarrito([]);
      setTotal(0);
      setNit('');
      setRazonSocial('');
      cargarProductos();
    } catch (error) {
      console.error(error);
      const msg = error.response?.data?.message || "Error desconocido";
      alert("❌ " + msg);
    } finally {
      setLoading(false);
    }
  };

  const buscarClientePorNit = async () => {
    if (!nit.trim()) return;
    try {
      const res = await axios.get(`/api/clientes/buscar/${nit}`);
      if (res.data.encontrado) {
        setRazonSocial(res.data.cliente.razon_social);
      }
    } catch (error) {
      console.error(error);
    }
  };

  return (
    <div className="ventas-container">
      <h2 className="ventas-title">Punto de Venta</h2>

      <div className="ventas-grid">

        {/* COLUMNA IZQUIERDA */}
        <div>

          {/* DATOS CLIENTE */}
          <div className="card">
            <h3 className="card-title">Datos del Cliente</h3>
            <div className="form-grid">
              <div className="form-group">
                <label>NIT / CI</label>
                <input
                  type="text"
                  placeholder="Ingrese NIT"
                  value={nit}
                  onChange={(e) => setNit(e.target.value)}
                  onBlur={buscarClientePorNit}
                />
              </div>
              <div className="form-group">
                <label>Razón Social / Nombre</label>
                <input
                  type="text"
                  placeholder="Ingrese Razón Social"
                  value={razonSocial}
                  onChange={(e) => setRazonSocial(e.target.value)}
                />
              </div>
            </div>
          </div>

          {/* AGREGAR PRODUCTOS */}
          <div className="card" style={{ marginTop: "20px" }}>
            <h3 className="card-title">Agregar Productos</h3>
            <div className="form-grid">

              <div className="form-group">
                <label>Producto</label>
                <select
                  value={productoSeleccionado}
                  onChange={(e) => setProductoSeleccionado(e.target.value)}
                  // Si hay error, pintamos el borde de rojo
                  className={!infoStock.esValido ? "input-error" : ""}
                >
                  <option value="">-- Seleccione --</option>
                  {productos.map(p => (
                    <option key={p.id} value={p.id} disabled={p.stock_total <= 0}>
                      {p.nombre} (Stock: {p.stock_total})
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Cantidad</label>
                <input
                  type="number"
                  min="1"
                  value={cantidad}
                  onChange={(e) => setCantidad(e.target.value)}
                  className={!infoStock.esValido ? "input-error" : ""}
                  

                />
              </div>
            </div>

            {/* MENSAJE DE FEEDBACK (Debajo de los inputs) */}
            <div className='columnconfirmacion'>
              {productoSeleccionado && infoStock.mensaje && (
                <div
                  className={`stock-info ${infoStock.esValido ? 'ok' : 'error'}`}
                >
                  {infoStock.mensaje}
                </div>
              )}


              <button
                onClick={agregarAlCarrito}
                // DESHABILITAMOS EL BOTÓN SI HAY ERROR O NO HAY SELECCIÓN
                disabled={!productoSeleccionado || !infoStock.esValido || cantidad <= 0}
                className="btn btn-primary"
                style={{
                  marginTop: "15px",
                  opacity: (!productoSeleccionado || !infoStock.esValido) ? 0.5 : 1,
                  cursor: (!productoSeleccionado || !infoStock.esValido) ? 'not-allowed' : 'pointer'
                }}
              >
                AGREGAR +
              </button>
            </div>

          </div>
        </div>

        {/* COLUMNA DERECHA (Carrito) */}
        <div className="card">
          <h3 className="card-title">Resumen de Venta</h3>

          {carrito.length === 0 ? (
            <p style={{ textAlign: "center", color: "#9ca3af", marginTop: "40px" }}>
              El carrito está vacío
            </p>
          ) : (
            <table className="tabla-carrito">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cant.</th>
                  <th>Precio</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {carrito.map((item, idx) => (
                  <tr key={idx}>
                    <td>{item.nombre}</td>
                    <td>{item.cantidad}</td>
                    <td>{item.precio}</td>
                    <td>{item.subtotal}</td>
                    <td>
                      <button
                        className="btn btn-danger"
                        onClick={() => eliminarItem(item.producto_id)}
                      >
                        X
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}

          <div className="total-box">
            <span>TOTAL:</span>
            <span>{total.toFixed(2)} Bs</span>
          </div>

          <button
            onClick={procesarVenta}
            disabled={loading || carrito.length === 0}
            className="btn btn-success"
            style={{ width: "100%", marginTop: "15px", padding: "14px" }}
          >
            {loading ? "PROCESANDO..." : "CONFIRMAR VENTA"}
          </button>
        </div>

      </div>
    </div>
  );
};

export default Ventas;