$('#downloadQrCodePdf').click(function() {
    const canvas = document.querySelector('#qrCode canvas');
    if (canvas) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: [50, 25] // 50mm x 25mm sticker size
        });
        const qrData = <?php echo $qr_data ? json_encode($qr_data) : 'null'; ?>;
        const imgData = canvas.toDataURL('image/png');

        // Add QR code
        doc.addImage(imgData, 'PNG', 4, 4, 17, 17); // QR code at top-left, 17mm x 17mm

        // Set font and styling
        doc.setFont('helvetica');
        doc.setFontSize(7); // Reduced font size to fit more content
        const maxWidth = 28; // Max width for text (50mm - 2mm margin - 17mm QR code - 3mm padding)
        let yPosition = 5; // Starting Y position for text
        const lineHeight = 2; // Line height for spacing

        // Function to wrap text with optional bold styling
        function wrapText(text, x, y, maxWidth, lineHeight, isBold = false) {
            if (isBold) {
                doc.setFont('helvetica', 'bold'); // Set bold font
            } else {
                doc.setFont('helvetica', 'normal'); // Set regular font
            }
            const words = text.split(' ');
            let line = '';
            const lines = [];
            for (let i = 0; i < words.length; i++) {
                const testLine = line + words[i] + ' ';
                const metrics = doc.getTextWidth(testLine);
                if (metrics > maxWidth && line !== '') {
                    lines.push(line);
                    line = words[i] + ' ';
                } else {
                    line = testLine;
                }
            }
            lines.push(line);
            lines.forEach((line, index) => {
                doc.text(line.trim(), x, y + index * lineHeight);
            });
            return lines.length * lineHeight; // Return total height used
        }

        // Function to truncate text to fit within maxWidth
        function truncateText(text, maxWidth) {
            let truncated = text;
            let metrics = doc.getTextWidth(text);
            if (metrics > maxWidth) {
                while (metrics > maxWidth - doc.getTextWidth('...') && truncated.length > 0) {
                    truncated = truncated.slice(0, -1);
                    metrics = doc.getTextWidth(truncated);
                }
                truncated = truncated.trim() + '...';
            }
            return truncated;
        }

        // Add text with wrapping
        if (qrData) {
            const fields = [
                { label: 'SN:', value: qrData.serial_number || '', boldLabel: true },
                { label: 'Tire Size & Brand:', value: qrData.description || '', boldLabel: true },
                { label: 'Date:', value: qrData.date || '', boldLabel: true },
                { label: 'Max Load:', value: qrData.maxload || 'kgs', boldLabel: true },
                { label: 'Made in Sri Lanka', value: '', boldLabel: false }
            ];

            fields.forEach((field, index) => {
                if (field.label) {
                    let xPosition = 22; // Starting X position for text
                    // Render the label
                    const labelWidth = doc.getTextWidth(field.label + ' ');
                    doc.setFont('helvetica', field.boldLabel ? 'bold' : 'normal');
                    doc.text(field.label, xPosition, yPosition);
                    xPosition += labelWidth; // Move X position after label

                    // Render the value in regular font
                    if (field.value) {
                        // Truncate Tire Size & Brand value if necessary
                        const valueToRender = index === 1 ? truncateText(field.value, maxWidth - labelWidth) : field.value;
                        const heightUsed = wrapText(valueToRender, xPosition, yPosition, maxWidth - labelWidth, lineHeight, false);
                        yPosition += heightUsed + 0.5; // Add small gap between fields
                    } else {
                        yPosition += lineHeight + 0.5; // Adjust for single-line label
                    }

                    if (yPosition > 23) {
                        console.warn('Text overflowed PDF height');
                        return; // Stop adding text if it exceeds page height
                    }
                }
            });
        }

        // Save the PDF
        doc.save('qrcode_' + (qrData ? qrData.serial_number.replace(/[^a-zA-Z0-9]/g, '_') : 'stock') + '.pdf');
    } else {
        console.error('QR code canvas not found for PDF download');
        alert('Error: Unable to generate PDF. Please try again.');
    }
});