// Comprobante.jsx
import React, { useEffect, useState } from 'react';
import axios from '../lib/axios';
import Select from 'react-select';

import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';



const generarPDF = (comprobante) => {
  import('jspdf').then(({ jsPDF }) => {
    import('jspdf-autotable').then(({ default: autoTable }) => {

      const doc = new jsPDF('p', 'mm', 'a4');
      const pageWidth = doc.internal.pageSize.getWidth();
      const marginLeft = 15;
      let y = 20;

      // --- Encabezado del comprobante ---
      doc.setFontSize(12);
      doc.setFont('helvetica', 'bold');
      doc.text("COMPROBANTE CONTABLE", pageWidth / 2, y, { align: "center" });
      y += 8;

      doc.setFont('helvetica', 'normal');
      doc.setFontSize(10);
      const fechaTexto = `Fecha: ${comprobante.fecha ? comprobante.fecha : ""}`;
      doc.text(`Tipo: ${comprobante.tipo || ""}`, marginLeft, y);
      doc.text(fechaTexto, pageWidth - marginLeft - doc.getTextWidth(fechaTexto), y);
      y += 8;

      doc.text(`Glosa general: ${comprobante.glosa_general || ""}`, marginLeft, y);
      y += 8;

      // --- Construcción de tabla ---
      const head = [["Código", "Glosa detalle", "Debe", "Haber"]];
      const body = [];
      const rowLevels = [];

      (comprobante.detalles || []).forEach((d) => {
        const cuenta = d.cuenta || {}; // en caso de que falte la relación
        const codigo = cuenta.codigo || "";
        const descripcion = cuenta.descripcion || d.glosa_detalle || "";
        const nivel = codigo.split(".").length; // determina jerarquía por puntos

        body.push([
          codigo,
          descripcion,
          (parseFloat(d.debe) || 0).toFixed(2),
          (parseFloat(d.haber) || 0).toFixed(2)
        ]);
        rowLevels.push(nivel);
      });

      // --- Totales ---
      const totalDebe = (comprobante.detalles || [])
        .reduce((acc, d) => acc + (parseFloat(d.debe) || 0), 0)
        .toFixed(2);
      const totalHaber = (comprobante.detalles || [])
        .reduce((acc, d) => acc + (parseFloat(d.haber) || 0), 0)
        .toFixed(2);

      body.push(["", "TOTALES", totalDebe, totalHaber]);
      rowLevels.push(1);

      // --- Render de la tabla ---
      autoTable(doc, {
        startY: y,
        head,
        body,
        styles: { fontSize: 10, cellPadding: 3 },
        headStyles: { fillColor: [52, 73, 94], textColor: 255, halign: "center" },
        didParseCell: function (data) {
          const rowIndex = data.row.index;
          const colIndex = data.column.index;

          // Formato de la última fila (totales)
          if (rowIndex === body.length - 1) {
            data.cell.styles.fontStyle = "bold";
            data.cell.styles.fillColor = [230, 230, 230];
          }

          // Sangría jerárquica
          if (colIndex === 0) {
            const nivel = rowLevels[rowIndex] || 1;
            const pad = Math.max(0, (nivel - 1) * 5); // sangría por nivel
            data.cell.styles.cellPadding = [3, 3, 3, pad];
            if (nivel <= 2) data.cell.styles.fontStyle = "bold";
          }

          // Alineación numérica
          if (colIndex === 2 || colIndex === 3) {
            data.cell.styles.halign = "right";
          }
        },
        columnStyles: {
          0: { cellWidth: 35 },  // Código
          1: { cellWidth: 85 },  // Glosa
          2: { cellWidth: 35 },  // Debe
          3: { cellWidth: 35 }   // Haber
        },
        margin: { left: 10, right: 10 },
        tableWidth: 'auto',
        overflow: 'linebreak',
        pageBreak: 'auto'
      });

      y = doc.lastAutoTable.finalY + 10;

      // --- Firmas ---
      const seccionFirmas = [
        { label: "Elaborado por:", x: marginLeft },
        { label: "Verificado por:", x: pageWidth / 2 - 20 },
        { label: "Aprobado por:", x: pageWidth - marginLeft - 60 }
      ];

      y += 10;
      seccionFirmas.forEach(f => {
        doc.text(f.label, f.x, y);
        doc.line(f.x, y + 15, f.x + 50, y + 15);
      });

      // --- Guardar PDF ---
      const nombreArchivo = `Comprobante_${comprobante.tipo}_${comprobante.id}.pdf`;
      doc.save(nombreArchivo);
    });
  });
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

    // 🔹 Validar glosa general obligatoria
    if (!nuevoComprobante.glosa_general.trim()) {
      alert("⚠️ La glosa general es obligatoria.");
      return;
    }

    if (totalDebe !== totalHaber) {
      alert("❌ El comprobante no está balanceado (Debe ≠ Haber).");
      return;
    }

    const detallesTransformados = nuevoComprobante.detalles.map(l => ({
      cuenta_id: l.cuenta_id,
      glosa_detalle: l.glosa_detalle,
      debe: l.tipo === 'debe' ? l.monto : 0,
      haber: l.tipo === 'haber' ? l.monto : 0
    }));

    axios.post('/api/comprobantes', {
      ...nuevoComprobante,
      detalles: detallesTransformados
    })
      .then(() => {
        axios.get('/api/comprobantes').then(r => setComprobantes(r.data));
        setShowForm(false);
        setNuevoComprobante({
          tipo: 'ingreso',
          fecha: new Date().toISOString().split("T")[0],
          glosa_general: '',
          detalles: [{ cuenta_id: '', glosa_detalle: '', tipo: 'debe', monto: '' }]
        });
      })
      .catch(err => console.error(err.response?.data));
  };

  const opcionesCuentas = planCuentas.map(c => ({
    value: c.id,
    label: `${c.codigo} - ${c.descripcion}`
  }));

  const { totalDebe, totalHaber } = calcularTotales();

  // 🔹 Estilo personalizado para react-select (modo oscuro visible)
  const customSelectStyles = {
    control: (provided) => ({
      ...provided,
      backgroundColor: '#2c2c2c',
      color: 'white',
      borderColor: '#555',
    }),
    singleValue: (provided) => ({
      ...provided,
      color: 'white',
    }),
    menu: (provided) => ({
      ...provided,
      backgroundColor: '#1f1f1f',
      color: 'white',
      zIndex: 9999,
    }),
    option: (provided, state) => ({
      ...provided,
      backgroundColor: state.isFocused ? '#333' : '#1f1f1f',
      color: 'white',
    }),
  };

  return (
    <div>
      <h2>Comprobantes</h2>
      <button onClick={() => setShowForm(true)}>➕ Nuevo Comprobante</button>

      {showForm && (
        <div style={{ border: '1px solid gray', padding: 10, marginTop: 10, borderRadius: 5 }}>
          {/* Tipo */}
          <div>
            <label>Tipo:</label>
            <select
              value={nuevoComprobante.tipo}
              onChange={e => setNuevoComprobante({ ...nuevoComprobante, tipo: e.target.value })}
            >
              <option value="ingreso">Ingreso</option>
              <option value="egreso">Egreso</option>
              <option value="diario">Diario</option>
            </select>
          </div>

          {/* Fecha */}
          <div>
            <label>Fecha:</label>
            <input
              type="date"
              value={nuevoComprobante.fecha}
              onChange={e => setNuevoComprobante({ ...nuevoComprobante, fecha: e.target.value })}
            />
          </div>

          {/* Glosa general */}
          <div>
            <label>Glosa general:</label>
            <input
              type="text"
              placeholder="Motivo del comprobante..."
              value={nuevoComprobante.glosa_general}
              onChange={e => setNuevoComprobante({ ...nuevoComprobante, glosa_general: e.target.value })}
              style={{ width: '100%' }}
              required // HTML required (no bloquea JS pero marca campo)
            />
          </div>

          {/* Cabecera tabla */}
          <div style={{
            display: 'grid',
            gridTemplateColumns: '2fr 2fr 1fr 1fr auto',
            gap: 5,
            marginTop: 10,
            fontWeight: 'bold',
            textAlign: 'center'
          }}>
            <div>Cuenta</div>
            <div>Glosa detalle</div>
            <div>Tipo</div>
            <div>Monto</div>
            <div>Acción</div>
          </div>

          {/* Filas dinámicas */}
          {nuevoComprobante.detalles.map((linea, index) => (
            <div
              key={index}
              style={{
                display: 'grid',
                gridTemplateColumns: '2fr 2fr 1fr 1fr auto',
                gap: 5,
                marginTop: 5,
                alignItems: 'center'
              }}
            >
              <Select
                options={opcionesCuentas}
                value={opcionesCuentas.find(opt => opt.value === linea.cuenta_id) || null}
                onChange={option => handleLineaChange(index, 'cuenta_id', option ? option.value : '')}
                placeholder="Selecciona cuenta"
                styles={customSelectStyles} // 🔹 estilo oscuro visible
              />

              <input
                type="text"
                placeholder="Glosa detalle"
                value={linea.glosa_detalle}
                onChange={e => handleLineaChange(index, 'glosa_detalle', e.target.value)}
              />

              <select
                value={linea.tipo}
                onChange={e => handleLineaChange(index, 'tipo', e.target.value)}
              >
                <option value="debe">Debe</option>
                <option value="haber">Haber</option>
              </select>

              <input
                type="number"
                placeholder="Monto"
                value={linea.monto}
                onChange={e => {
                  const valor = e.target.value.replace(/[^0-9.]/g, '');
                  handleLineaChange(index, 'monto', valor);
                }}
                onFocus={e => {
                  if (e.target.value === "0") e.target.value = "";
                }}
                onBlur={e => {
                  if (e.target.value === "") e.target.value = "0";
                }}
                onWheel={e => e.target.blur()} // evita scroll accidental
                min="0"
                step="any"
                style={{
                  MozAppearance: 'textfield', // quita los botones ↑↓ en Firefox
                  appearance: 'textfield',    // quita los botones ↑↓ en Chrome/Edge
                }}
              />


              {/* Quitar botones de número en navegadores basados en WebKit */}
              <style>
                {`input[type=number]::-webkit-outer-spin-button,
                  input[type=number]::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                  }`}
              </style>

              <button
                onClick={() => eliminarLinea(index)}
                style={{
                  background: 'red',
                  color: 'white',
                  border: 'none',
                  borderRadius: '5px',
                  cursor: 'pointer'
                }}
              >
                ❌
              </button>
            </div>
          ))}

          {/* Totales */}
          <div style={{ marginTop: 15, fontWeight: 'bold' }}>
            <div>Total Debe: {totalDebe.toFixed(2)}</div>
            <div>Total Haber: {totalHaber.toFixed(2)}</div>
            {totalDebe === totalHaber ? (
              <div style={{ color: 'green' }}>✅ Comprobante balanceado</div>
            ) : (
              <div style={{ color: 'red' }}>⚠️ El comprobante no está balanceado</div>
            )}
          </div>

          <button onClick={agregarLinea}>Agregar línea</button>
          <button onClick={guardarComprobante}>Guardar</button>
        </div>
      )}

      {/* 🔹 Tabla de comprobantes con campo para futuro PDF */}
      <table style={{ marginTop: 10, borderCollapse: 'collapse', width: '100%', border: '1px solid #555' }}>
        <thead style={{ background: '#222', color: 'white' }}>
          <tr>
            <th style={{ border: '1px solid #555', padding: 5 }}>ID</th>
            <th style={{ border: '1px solid #555', padding: 5 }}>Tipo</th>
            <th style={{ border: '1px solid #555', padding: 5 }}>Fecha</th>
            <th style={{ border: '1px solid #555', padding: 5 }}>Glosa</th>
            <th style={{ border: '1px solid #555', padding: 5 }}>Acción</th>
          </tr>
        </thead>
        <tbody>
          {comprobantes.map(c => (
            <tr key={c.id} style={{ textAlign: 'center', background: '#2a2a2a', color: 'white' }}>
              <td style={{ border: '1px solid #555', padding: 5 }}>{c.id}</td>
              <td style={{ border: '1px solid #555', padding: 5 }}>{c.tipo}</td>
              <td style={{ border: '1px solid #555', padding: 5 }}>{c.fecha}</td>
              <td style={{ border: '1px solid #555', padding: 5 }}>{c.glosa_general || '(sin glosa)'}</td>
              <td style={{ border: '1px solid #555', padding: 5 }}>
                <button
                  style={{
                    background: '#007bff',
                    color: 'white',
                    border: 'none',
                    borderRadius: '4px',
                    cursor: 'pointer',
                    padding: '5px 10px'
                  }}
                  onClick={() => generarPDF(c)}
                >
                  📄 Generar PDF
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default Comprobante;
