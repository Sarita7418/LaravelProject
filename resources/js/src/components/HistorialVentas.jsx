import React, { useState, useEffect } from 'react';
import axios from 'axios';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import QRCode from 'qrcode'; // 👈 Asegúrate de instalar: npm install qrcode
import "./Ventas.css";

// Función para convertir números a letras (Reutilizamos la lógica contable)
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

    if (entero === 0) return `CERO ${centavos}/100 BOLIVIANOS`;
    if (entero === 100) return `CIEN ${centavos}/100 BOLIVIANOS`;

    if (entero >= 1000) {
        let miles = Math.floor(entero / 1000);
        entero = entero % 1000;
        if (miles === 1) letras += "MIL ";
        else {
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

    return `${letras.trim()} ${centavos}/100 BOLIVIANOS`;
};

const HistorialVentas = () => {
    const [facturas, setFacturas] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        cargarHistorial();
    }, []);

    const cargarHistorial = async () => {
        try {
            const res = await axios.get('/api/facturas');
            setFacturas(res.data);
        } catch (error) {
            console.error("Error cargando historial:", error);
        } finally {
            setLoading(false);
        }
    };

    // --- FUNCIÓN GENERAR PDF (Opción 3: Diseño Limpio) ---
   // --- FUNCIÓN GENERAR PDF (CORREGIDA: CUF LEGIBLE Y TEXTO ALINEADO) ---
    const generarFacturaPDF = async (factura) => {
        try {
            let detallesFactura = factura.detalles || [];
            if(detallesFactura.length === 0) console.warn("Sin detalles");

            const doc = new jsPDF('p', 'mm', 'letter'); 
            const pageWidth = doc.internal.pageSize.getWidth();
            const marginL = 15; 
            const marginR = pageWidth - 15; 
            
            // Fuentes
            const fontSizeStandard = 9;
            const fontSizeTitle = 11; 
            const fontSizeBig = 14;   

            // Definimos columnas fijas para el bloque derecho
            // colEtiquetas: Donde dice "NIT:", "CUF:"
            // colValores: Donde empieza el texto del valor (para que quede como bloque)
            const colEtiquetas = 130; 
            const colValores = 152; // Damos 22mm para la etiqueta
            const anchoTextoDerecha = marginR - colValores; // Espacio disponible para el texto (~49mm)

            // ==========================================
            // 1. CABECERA
            // ==========================================
            
            // --- IZQUIERDA (EMPRESA) ---
            doc.setFontSize(fontSizeTitle);
            doc.setFont('helvetica', 'bold');
            doc.text("MI EMPRESA S.A.", marginL, 20); 
            
            doc.setFontSize(fontSizeStandard);
            doc.setFont('helvetica', 'bold');
            doc.text("CASA MATRIZ", marginL, 25);
            
            doc.setFont('helvetica', 'normal');
            doc.text("Calle Falsa 123 #54 - Zona Sopocachi", marginL, 29);
            doc.text("Teléfono: 222-3333", marginL, 33);
            doc.text("La Paz - Bolivia", marginL, 37);

            // --- DERECHA (FISCAL) ---
            let yFiscal = 20;

            // NIT
            doc.setFontSize(fontSizeStandard);
            doc.setFont('helvetica', 'bold');
            doc.text("NIT:", colEtiquetas, yFiscal);
            doc.setFont('helvetica', 'normal');
            // Alineamos a la IZQUIERDA partiendo de colValores -> Efecto bloque limpio
            doc.text("1020304050", colValores, yFiscal, { align: 'left' });

            // FACTURA
            yFiscal += 5;
            doc.setFont('helvetica', 'bold');
            doc.text("FACTURA N°:", colEtiquetas, yFiscal);
            doc.setFont('helvetica', 'normal');
            doc.text(factura.numero_factura.toString(), colValores, yFiscal, { align: 'left' });

            // CUF (CORREGIDO: TAMAÑO DECENTE)
            yFiscal += 5;
            doc.setFont('helvetica', 'bold');
            doc.text("CUF:", colEtiquetas, yFiscal);
            
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8); // Tamaño 8 es legible y entra bien
            // Cortamos el texto para que entre en la columna
            const cufLines = doc.splitTextToSize(factura.cuf || "---", anchoTextoDerecha);
            doc.text(cufLines, colValores, yFiscal, { align: 'left' }); // Bloque alineado a la izq
            
            // Ajuste dinámico de altura según líneas del CUF
            yFiscal += (cufLines.length * 3) + 2; 

            // ACTIVIDAD (CORREGIDO: ALINEACIÓN Y ANCHO)
            doc.setFontSize(fontSizeStandard); // Volvemos a tamaño 9
            doc.setFont('helvetica', 'bold');
            doc.text("ACTIVIDAD:", colEtiquetas, yFiscal);
            
            doc.setFont('helvetica', 'normal');
            const actividadTexto = "Venta de abarrotes, suministros y diversos productos sin especialización";
            // Cortamos el texto exactamente al ancho disponible
            const actividadLines = doc.splitTextToSize(actividadTexto, anchoTextoDerecha);
            
            // align: 'left' en la columna de valores crea el efecto "Justificado" visual (borde izquierdo recto)
            doc.text(actividadLines, colValores, yFiscal, { align: 'left' }); 


            // ==========================================
            // 2. TÍTULO FACTURA
            // ==========================================
            // Calculamos Y para no chocar si la actividad fue muy larga
            let yTitulo = Math.max(50, yFiscal + (actividadLines.length * 3) + 5);
            
            doc.setFontSize(fontSizeBig);
            doc.setFont('helvetica', 'bold');
            doc.text("FACTURA", pageWidth / 2, yTitulo, { align: "center" });
            
            doc.setFontSize(8);
            doc.text("(Con Derecho a Crédito Fiscal)", pageWidth / 2, yTitulo + 5, { align: "center" });

            // ==========================================
            // 3. DATOS DEL CLIENTE
            // ==========================================
            let yInfo = yTitulo + 15;
            const fechaFormateada = new Date(factura.created_at).toLocaleString();

            doc.setFontSize(fontSizeStandard);

            // Fila 1
            doc.setFont('helvetica', 'bold');
            doc.text("Fecha:", marginL, yInfo);
            doc.setFont('helvetica', 'normal');
            doc.text(fechaFormateada, marginL + 15, yInfo); 

            doc.setFont('helvetica', 'bold');
            doc.text("NIT/CI/CEX:", 130, yInfo); 
            doc.setFont('helvetica', 'normal');
            doc.text(factura.cliente?.nit_ci || "0", 152, yInfo, { align: 'left' }); // Alineado con el bloque de arriba

            // Fila 2
            yInfo += 6;
            doc.setFont('helvetica', 'bold');
            doc.text("Nombre/Razón Social:", marginL, yInfo);
            doc.setFont('helvetica', 'normal');
            doc.text(factura.cliente?.razon_social || "S/N", marginL + 40, yInfo);


            // ==========================================
            // 4. TABLA
            // ==========================================
            const tableBody = detallesFactura.map(det => [
                det.producto?.codigo_interno || "PROD-"+det.producto_id,
                det.cantidad,
                det.producto?.nombre || "Producto desconocido",
                parseFloat(det.precio_unitario).toFixed(2),
                "0.00",
                parseFloat(det.subtotal).toFixed(2)
            ]);

            autoTable(doc, {
                startY: yInfo + 8,
                head: [['CÓDIGO', 'CANT.', 'DESCRIPCIÓN', 'P. UNITARIO', 'DESC.', 'SUBTOTAL']],
                body: tableBody,
                theme: 'plain',
                styles: { fontSize: 8, cellPadding: 2, lineWidth: 0.1, lineColor: [0, 0, 0], valign: 'middle', font: 'helvetica' },
                headStyles: { fillColor: [240, 240, 240], textColor: 0, fontStyle: 'bold', halign: 'center', lineWidth: 0.1, lineColor: [0,0,0] },
                columnStyles: {
                    0: { halign: 'left', cellWidth: 25 },
                    1: { halign: 'center', cellWidth: 15 },
                    2: { halign: 'left', cellWidth: 'auto' },
                    3: { halign: 'right', cellWidth: 25 },
                    4: { halign: 'right', cellWidth: 20 },
                    5: { halign: 'right', cellWidth: 25 },
                },
                foot: [
                    ['', '', '', '', 'TOTAL Bs', parseFloat(factura.monto_total).toFixed(2)],
                    ['', '', '', '', 'IMPORTE BASE', parseFloat(factura.monto_total).toFixed(2)]
                ],
                footStyles: { fillColor: [255, 255, 255], textColor: 0, fontStyle: 'bold', halign: 'right', lineWidth: 0.1, lineColor: [0,0,0] }
            });

            let finalY = doc.lastAutoTable.finalY + 10;

            // ==========================================
            // 5. LITERAL Y PIE
            // ==========================================
            
            doc.setFontSize(fontSizeStandard);
            doc.setFont('helvetica', 'bold');
            doc.text("Son:", marginL, finalY);
            doc.setFont('helvetica', 'normal');
            const literal = numeroALetras(factura.monto_total);
            doc.text(literal, marginL + 10, finalY);

            finalY += 10;
            
            doc.setFontSize(6);
            const leyenda1 = '"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"';
            const leyenda2 = 'Ley N° 453: Tienes derecho a recibir información sobre las características y contenidos de los servicios que utilices.';
            
            doc.text(leyenda1, marginL, finalY + 10, { maxWidth: 120 });
            doc.text(leyenda2, marginL, finalY + 14, { maxWidth: 120 });

            const qrSize = 30;
            const qrData = `${factura.cuf}|${factura.numero_factura}|${factura.monto_total}|${fechaFormateada}`;
            const qrDataUrl = await QRCode.toDataURL(qrData);
            doc.addImage(qrDataUrl, 'PNG', marginR - qrSize, finalY, qrSize, qrSize); 

            doc.save(`Factura_${factura.numero_factura}.pdf`);

        } catch (error) {
            console.error(error);
            alert("Error generando PDF.");
        }
    };

    return (
        <div className="ventas-container">
            <h2 className="ventas-title">Historial de Ventas</h2>
            <div className="card">
                <h3 className="card-title">Listado de Facturas</h3>
                {loading ? (
                    <p className="p-5 text-center text-gray-500">Cargando...</p>
                ) : facturas.length === 0 ? (
                    <p className="p-5 text-center text-gray-500">Sin registros.</p>
                ) : (
                    <table className="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>NIT</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            {facturas.map((f) => (
                                <tr key={f.id}>
                                    <td>#{f.numero_factura}</td>
                                    <td>{new Date(f.created_at).toLocaleDateString()}</td>
                                    <td>{f.cliente?.razon_social}</td>
                                    <td>{f.cliente?.nit_ci}</td>
                                    <td>{parseFloat(f.monto_total).toFixed(2)}</td>
                                    <td>
                                        <span style={{ 
                                            color: f.estado === 'VALIDA' ? 'green' : 'red',
                                            fontWeight: 'bold' 
                                        }}>
                                            {f.estado}
                                        </span>
                                    </td>
                                    <td>
                                        <button 
                                            className="btn btn-primary" 
                                            style={{ padding: '5px 10px', fontSize: '12px', width: 'auto' }}
                                            onClick={() => generarFacturaPDF(f)}
                                        >
                                            📄 PDF
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </div>
    );
};

export default HistorialVentas;