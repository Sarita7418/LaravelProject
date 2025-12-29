import React, { useEffect, useState } from 'react';
import axios from '../lib/axios';
import Select from 'react-select';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import './Comprobante.css'; // <--- IMPORTANTE: Importamos el CSS


// Función auxiliar para convertir números a letras (Formato Contable Boliviano)
const numeroALetras = (num) => {
  const unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
  const decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
  const diez_veinte = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
  const centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

  let n = parseFloat(num).toFixed(2);
  let partes = n.split('.');
  let entero = parseInt(partes[0]);
  let centavos = partes[1];
  let letras = "";

  if (entero === 0) return `(CERO ${centavos}/100 BOLIVIANOS)`;
  if (entero === 100) return `(CIEN ${centavos}/100 BOLIVIANOS)`;

  if (entero >= 1000) {
    let miles = Math.floor(entero / 1000);
    entero = entero % 1000;
    if (miles === 1) letras += "MIL ";
    else {
      let milesStr = miles.toString();
      if (miles < 10) letras += unidades[miles] + " MIL ";
      else if (miles < 20) letras += diez_veinte[miles - 10] + " MIL ";
      else letras += miles + " MIL ";
    }
  }

  if (entero >= 100) {
    let cent = Math.floor(entero / 100);
    if (entero === 100) letras += "CIEN ";
    else letras += centenas[cent] + " ";
    entero = entero % 100;
  }

  if (entero >= 20) {
    let dec = Math.floor(entero / 10);
    entero = entero % 10;
    letras += decenas[dec];
    if (entero > 0) letras += " Y " + unidades[entero];
  } else if (entero >= 10) {
    letras += diez_veinte[entero - 10];
  } else if (entero > 0) {
    letras += unidades[entero];
  }

  // Agregamos paréntesis y mayúsculas, formato estándar
  return `(${letras.trim()} ${centavos}/100 BOLIVIANOS)`;
};


const generarPDF = (comprobante, planCuentas) => {
  try {
    const doc = new jsPDF('p', 'mm', 'a4');
    const pageWidth = doc.internal.pageSize.getWidth();
    let y = 20;

    // --- TÍTULO ---
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text("COMPROBANTE", pageWidth / 2, y, { align: "center" });
    y += 10;

    // --- CABECERA ---
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    
    const tipo = comprobante.tipo ? comprobante.tipo.toUpperCase() : "---";
    const fecha = comprobante.fecha || "---";
    doc.text(`Tipo: ${tipo}`, 15, y);
    doc.text(`Fecha: ${fecha}`, pageWidth - 15, y, { align: "right" });
    y += 7;

    const glosaGral = comprobante.glosa_general || "---";
    doc.text(`Glosa General: ${glosaGral}`, 15, y);
    y += 10;

    // --- PROCESAMIENTO DE FILAS ---
    const body = [];
    
    const obtenerCodigoPadre = (codigo) => {
      if (!codigo) return null;
      const partes = codigo.split('.');
      if (partes.length <= 1) return null; 
      partes.pop(); 
      return partes.join('.');
    };

    (comprobante.detalles || []).forEach(d => {
      const codigoCuenta = d.cuenta ? d.cuenta.codigo : "";
      const nombreCuenta = d.cuenta ? d.cuenta.descripcion : ""; 
      const glosaUsuario = d.glosa_detalle || ""; 
      
      // 1. FILA PADRE (NIVEL 1)
      if (planCuentas && planCuentas.length > 0) {
        const codigoPadre = obtenerCodigoPadre(codigoCuenta);
        const cuentaPadre = planCuentas.find(c => c.codigo === codigoPadre);
        
        if (cuentaPadre) {
          const ultimaFila = body[body.length - 1];
          // Comprobamos si la fila anterior ya tiene este código (ojo, ahora es un objeto, accedemos a .content)
          const ultimoCodigo = ultimaFila ? (ultimaFila[0].content || ultimaFila[0]) : "";
          
          if (ultimoCodigo !== cuentaPadre.codigo) {
             body.push([
               // CÓDIGO PADRE EN NEGRITA
               { content: cuentaPadre.codigo, styles: { fontStyle: 'bold' } }, 
               
               // DESCRIPCIÓN PADRE EN NEGRITA
               { content: cuentaPadre.descripcion.toUpperCase(), styles: { fontStyle: 'bold', cellPadding: {top:1, bottom:1, left:2} } },
               "", 
               ""
             ]);
          }
        }
      }

      // 2. FILA HIJA (NIVEL 2 - CUENTA CONTABLE)
      body.push([
        // CÓDIGO HIJO EN NEGRITA (CORREGIDO)
        { content: codigoCuenta, styles: { fontStyle: 'bold' } }, // <--- AQUÍ ESTABA EL DETALLE

        // DESCRIPCIÓN HIJA EN NEGRITA CON SANGRÍA
        { 
          content: nombreCuenta, 
          styles: { 
            fontStyle: 'bold', 
            cellPadding: { top: 1, bottom: 1, left: 8 } 
          } 
        },
        d.debe > 0 ? parseFloat(d.debe).toFixed(2) : "",
        d.haber > 0 ? parseFloat(d.haber).toFixed(2) : ""
      ]);

      // 3. FILA DETALLE (NIVEL 3 - GLOSA DE USUARIO)
      if (glosaUsuario) {
        body.push([
          "", 
          { 
            content: glosaUsuario, 
            styles: { 
              fontStyle: 'italic', 
              textColor: [60, 60, 60], 
              cellPadding: { top: 1, bottom: 2, left: 15 } 
            } 
          }, 
          "", 
          "", 
        ]);
      }
    });

    // TOTALES
    const totalDebe = (comprobante.detalles || []).reduce((acc, d) => acc + parseFloat(d.debe || 0), 0);
    const totalHaber = (comprobante.detalles || []).reduce((acc, d) => acc + parseFloat(d.haber || 0), 0);
    
    body.push([
      "", 
      { content: "TOTALES", styles: { halign: 'right', fontStyle: 'bold' } }, 
      { content: totalDebe.toFixed(2), styles: { fontStyle: 'bold' } }, 
      { content: totalHaber.toFixed(2), styles: { fontStyle: 'bold' } }
    ]);

    // --- TABLA ---
    autoTable(doc, {
      startY: y,
      head: [["Código", "Glosa / Descripción", "Debe", "Haber"]],
      body: body,
      theme: 'plain', 
      styles: { fontSize: 9, cellPadding: 2, valign: 'middle', lineColor: [200, 200, 200], lineWidth: 0.1 },
      headStyles: { fillColor: [15, 52, 96], textColor: 255, halign: 'center', fontStyle: 'bold' },
      columnStyles: {
        0: { cellWidth: 30 }, 
        1: { cellWidth: 'auto' }, 
        2: { cellWidth: 30, halign: 'right' }, 
        3: { cellWidth: 30, halign: 'right' }  
      },
    });

    // --- LITERAL ---
    const literal = numeroALetras(totalDebe);
    
    y = doc.lastAutoTable.finalY + 10;
    doc.setFontSize(9);
    
    doc.setFont('helvetica', 'bold');
    doc.text("Son:", 15, y);
    
    doc.setFont('helvetica', 'normal');
    doc.text(literal, 25, y); 

    // --- FIRMAS ---
    y += 30;
    if (y > 270) { doc.addPage(); y = 40; }

    doc.setLineWidth(0.5);
    doc.line(20, y, 70, y);  doc.text("Elaborado por", 45, y + 5, { align: "center" });
    doc.line(80, y, 130, y); doc.text("Aprobado por", 105, y + 5, { align: "center" });
    doc.line(140, y, 190, y); doc.text("Verificado por", 165, y + 5, { align: "center" });

    doc.save(`Comprobante_${comprobante.id}.pdf`);

  } catch (error) {
    console.error(error);
    alert("Error al generar PDF.");
  }
};

const Comprobante = () => {
  const [comprobantes, setComprobantes] = useState([]);
  const [planCuentas, setPlanCuentas] = useState([]);
  const [showForm, setShowForm] = useState(false);

  const [nuevoComprobante, setNuevoComprobante] = useState({
    tipo: 'ingreso',
    fecha: new Date().toISOString().split("T")[0],
    glosa_general: '',
    detalles: [
      { cuenta_id: '', glosa_detalle: '', tipo: 'debe', monto: 0 }
    ]
  });

  useEffect(() => {
    axios.get('/api/comprobantes').then(res => setComprobantes(res.data));
    axios.get('/api/cuentas').then(res => setPlanCuentas(res.data));
  }, []);

  // ... (Tus funciones agregarLinea, eliminarLinea, handleLineaChange, calcularTotales, guardarComprobante siguen IGUAL) ...
  const agregarLinea = () => {
    setNuevoComprobante(prev => ({
      ...prev,
      detalles: [...prev.detalles, { cuenta_id: '', glosa_detalle: '', tipo: 'debe', monto: 0 }]
    }));
  };

  const eliminarLinea = (index) => {
    setNuevoComprobante(prev => {
      if (prev.detalles.length <= 1) {
        alert("⚠️ Debe existir al menos una línea en el comprobante.");
        return prev;
      }
      const updated = prev.detalles.filter((_, i) => i !== index);
      return { ...prev, detalles: updated };
    });
  };

  const handleLineaChange = (index, campo, valor) => {
    const updated = [...nuevoComprobante.detalles];
    updated[index][campo] = valor;
    setNuevoComprobante({ ...nuevoComprobante, detalles: updated });
  };

  const calcularTotales = () => {
    let totalDebe = 0, totalHaber = 0;
    nuevoComprobante.detalles.forEach(l => {
      if (l.tipo === 'debe') totalDebe += parseFloat(l.monto) || 0;
      else totalHaber += parseFloat(l.monto) || 0;
    });
    return { totalDebe, totalHaber };
  };

  const guardarComprobante = () => {
    const { totalDebe, totalHaber } = calcularTotales();
    if (!nuevoComprobante.glosa_general.trim()) { alert("⚠️ Glosa obligatoria."); return; }
    if (totalDebe !== totalHaber) { alert("❌ Desbalanceado."); return; }

    const detallesTransformados = nuevoComprobante.detalles.map(l => ({
      cuenta_id: l.cuenta_id,
      glosa_detalle: l.glosa_detalle,
      debe: l.tipo === 'debe' ? l.monto : 0,
      haber: l.tipo === 'haber' ? l.monto : 0
    }));

    axios.post('/api/comprobantes', { ...nuevoComprobante, detalles: detallesTransformados })
      .then(() => {
        axios.get('/api/comprobantes').then(r => setComprobantes(r.data));
        setShowForm(false);
        setNuevoComprobante({
          tipo: 'ingreso', fecha: new Date().toISOString().split("T")[0], glosa_general: '',
          detalles: [{ cuenta_id: '', glosa_detalle: '', tipo: 'debe', monto: '' }]
        });
      })
      .catch(err => console.error(err.response?.data));
  };

  const opcionesCuentas = planCuentas.map(c => ({
    value: c.id, label: `${c.codigo} - ${c.descripcion}`
  }));

  const { totalDebe, totalHaber } = calcularTotales();

  // NOTA: Este objeto se queda en JS porque React-Select usa estilos en línea para su lógica interna
  const customSelectStyles = {
    control: (provided) => ({
      ...provided, backgroundColor: '#2c2c2c', color: 'white', borderColor: '#555', minHeight: '38px',
    }),
    singleValue: (provided) => ({ ...provided, color: 'white' }),
    input: (provided) => ({ ...provided, color: 'white' }),
    placeholder: (provided) => ({ ...provided, color: '#aaaaaa' }),
    menu: (provided) => ({ ...provided, backgroundColor: '#1f1f1f', color: 'white', zIndex: 9999 }),
    option: (provided, state) => ({
      ...provided, backgroundColor: state.isFocused ? '#333' : '#1f1f1f', color: 'white', cursor: 'pointer',
    }),
  };

  return (
    <div className="comprobante-container">

      {/* Título y Botón */}
      <div className="header-top">
        <h2 className="header-title">Comprobantes</h2>
        <button
          onClick={() => setShowForm(!showForm)}
          className={`btn-main-action ${showForm ? 'btn-cancel' : 'btn-new'}`}
        >
          {showForm ? 'Cancelar Operación' : '➕ Nuevo Comprobante'}
        </button>
      </div>

      {/* Formulario */}
      {showForm && (
        <div className="form-card">

          {/* Cabecera Inputs */}
          <div className="form-header-grid">
            <div>
              <label className="form-label">TIPO</label>
              <select
                className="form-input"
                value={nuevoComprobante.tipo}
                onChange={e => setNuevoComprobante({ ...nuevoComprobante, tipo: e.target.value })}
              >
                <option value="ingreso">Ingreso</option>
                <option value="egreso">Egreso</option>
                <option value="diario">Diario</option>
              </select>
            </div>
            <div>
              <label className="form-label">FECHA</label>
              <input
                type="date"
                className="form-input"
                value={nuevoComprobante.fecha}
                onChange={e => setNuevoComprobante({ ...nuevoComprobante, fecha: e.target.value })}
              />
            </div>
            <div>
              <label className="form-label">GLOSA GENERAL</label>
              <input
                type="text"
                className="form-input"
                placeholder="Ej: Compra de materiales..."
                value={nuevoComprobante.glosa_general}
                onChange={e => setNuevoComprobante({ ...nuevoComprobante, glosa_general: e.target.value })}
              />
            </div>
          </div>

          <hr className="divider" />

          {/* Encabezados Grid */}
          <div className="table-header">
            <div>Cuenta Contable</div>
            <div>Glosa Específica</div>
            <div>Tipo</div>
            <div style={{ textAlign: 'center' }}>Monto (Bs)</div>
            <div style={{ textAlign: 'center' }}>Eliminar</div>
          </div>

          {/* Filas */}
          <div className="rows-container">
            {nuevoComprobante.detalles.map((linea, index) => (
              <div key={index} className="table-row">
                <Select
                  options={opcionesCuentas}
                  value={opcionesCuentas.find(opt => opt.value === linea.cuenta_id) || null}
                  onChange={option => handleLineaChange(index, 'cuenta_id', option ? option.value : '')}
                  placeholder="Buscar cuenta..."
                  styles={customSelectStyles}
                />

                <input
                  type="text"
                  className="input-simple"
                  placeholder="Descripción..."
                  value={linea.glosa_detalle}
                  onChange={e => handleLineaChange(index, 'glosa_detalle', e.target.value)}
                />

                <select
                  className="input-simple"
                  value={linea.tipo}
                  onChange={e => handleLineaChange(index, 'tipo', e.target.value)}
                >
                  <option value="debe">Debe</option>
                  <option value="haber">Haber</option>
                </select>

                <input
                  type="number"
                  className="input-simple input-monto"
                  placeholder="0.00"
                  value={linea.monto}
                  onChange={e => handleLineaChange(index, 'monto', e.target.value)}
                  onFocus={e => e.target.value === "0" && (e.target.value = "")}
                  onBlur={e => e.target.value === "" && (e.target.value = "0")}
                />

                <button
                  className="btn-delete"
                  onClick={() => eliminarLinea(index)}
                  title="Eliminar línea"
                >
                  ×
                </button>
              </div>
            ))}
          </div>

          <button onClick={agregarLinea} className="btn-add-line">
            + Agregar nueva línea
          </button>

          {/* Totales */}
          <div className="form-footer">
            <div className="totales-group">
              <div>
                <div className="total-label">TOTAL DEBE</div>
                <div className="total-value">{totalDebe.toFixed(2)}</div>
              </div>
              <div>
                <div className="total-label">TOTAL HABER</div>
                <div className="total-value">{totalHaber.toFixed(2)}</div>
              </div>
              <div className="status-indicator">
                {totalDebe === totalHaber && totalDebe > 0 ? (
                  <span className="status-balanced">✅ Balanceado</span>
                ) : (
                  <span className="status-unbalanced">⚠️ No balanceado</span>
                )}
              </div>
            </div>

            <button
              onClick={guardarComprobante}
              disabled={totalDebe !== totalHaber || totalDebe === 0}
              className="btn-save"
            >
              💾 Guardar Comprobante
            </button>
          </div>
        </div>
      )}

      {/* Lista */}
      <h3 className="list-title">Comprobantes Registrados</h3>

      <div className="table-container">
        <table className="main-table">
          <thead>
            <tr>
              <th style={{ width: '50px' }}>ID</th>
              <th style={{ width: '120px' }}>Tipo</th>
              <th style={{ width: '120px' }}>Fecha</th>
              <th>Glosa</th>
              <th style={{ textAlign: 'center', width: '150px' }}>Acción</th>
            </tr>
          </thead>
          <tbody>
            {comprobantes.map((c) => (
              <tr key={c.id}>
                <td style={{ fontWeight: 'bold', color: '#555' }}>{c.id}</td>
                <td>
                  <span className={`badge badge-${c.tipo}`}>
                    {c.tipo}
                  </span>
                </td>
                <td style={{ color: '#555' }}>{c.fecha}</td>
                <td style={{ fontWeight: '500' }}>{c.glosa_general}</td>
                <td>
                  <button
                    onClick={() => generarPDF(c, planCuentas)} // <--- AGREGAMOS planCuentas AQUÍ
                    className="btn-download"
                  >
                    📄 Descargar
                  </button>
                </td>
              </tr>
            ))}
            {comprobantes.length === 0 && (
              <tr><td colSpan="5" style={{ textAlign: 'center', padding: '30px', color: '#888' }}>Sin registros.</td></tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Comprobante;