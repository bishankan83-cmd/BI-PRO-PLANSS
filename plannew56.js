const mysql = require('mysql');

// Create a MySQL database connection
const conn = mysql.createConnection({
  host: 'localhost:3306',
  user: 'planatir_task_managemen',
  password: 'Bishan@1919',
  database: 'planatir_task_managemen'
});

// Connect to the database
conn.connect((err) => {
  if (err) {
    console.error('Connection failed:', err);
    return;
  }

  console.log('Connected to the database');

  // Fetch customer names for each ERP from the 'worder' table
  const customerNames = {};

  const customerQuery = 'SELECT erp, Customer FROM worder';
  conn.query(customerQuery, (err, customerResults) => {
    if (err) {
      console.error('Query failed:', err);
      conn.end();
      return;
    }

    customerResults.forEach((row) => {
      const erp = row.erp;
      const customerName = row.Customer;
      customerNames[erp] = customerName;
    });

    // Retrieve all tire IDs, quantities, press, mold, and time_taken from the database table
    const tireQuery = `
      SELECT s.icode, s.tires_per_mold, s.mold_id, s.cavity_id, t.time_taken, p.erp, m.availability_date AS mold_availability, c.availability_date AS cavity_availability
      FROM process s
      INNER JOIN tire t ON s.icode = t.icode
      INNER JOIN tobeplan p ON s.icode = p.icode
      LEFT JOIN mold m ON s.mold_id = m.mold_id
      LEFT JOIN cavity c ON s.cavity_id = c.cavity_id
    `;

    conn.query(tireQuery, (err, tireResults) => {
      if (err) {
        console.error('Query failed:', err);
        conn.end();
        return;
      }

      if (tireResults.length > 0) {
        const latestEndDates = {};

        tireResults.forEach((row) => {
          const icode = row.icode;
          const tobe = row.tires_per_mold;
          const mold = row.mold_id;
          const cavity = row.cavity_id;
          const timeTaken = row.time_taken;
          const erpNumber = row.erp;
          const moldAvailability = row.mold_availability;
          const cavityAvailability = row.cavity_availability;

          const customerName = customerNames[erpNumber];

          const tireAvailability = new Date(); // Initialize with the current time
          const tireQuery = `SELECT availability_date FROM tire WHERE icode = '${icode}'`;

          conn.query(tireQuery, (err, tireResult) => {
            if (err) {
              console.error('Query failed:', err);
              conn.end();
              return;
            }

            if (tireResult.length > 0) {
              tireAvailability = tireResult[0].availability_date;
            }

            if (tobe === 0) {
              return; // Skip the tire
            }

            const startDate = new Date(Math.max(
              latestEndDates[mold] || moldAvailability,
              latestEndDates[cavity] || cavityAvailability,
              tireAvailability
            ));

            const totalMinutes = timeTaken * tobe;
            startDate.setMinutes(startDate.getMinutes() + totalMinutes);

            const endDate = startDate;

            latestEndDates[mold] = endDate;
            latestEndDates[cavity] = endDate;

            const insertQuery = `
              INSERT INTO plannew (icode, mold_id, cavity_id, start_date, end_date, erp, tires_per_mold, Customer)
              VALUES ('${icode}', '${mold}', '${cavity}', '${startDate.toISOString().slice(0, 19).replace('T', ' ')}', '${endDate.toISOString().slice(0, 19).replace('T', ' ')}', '${erpNumber}', '${tobe}', '${customerName}')
            `;

            conn.query(insertQuery, (err) => {
              if (err) {
                console.error('Query failed:', err);
              }
            });

            const updateMoldQuery = `UPDATE mold SET availability_date = '${endDate.toISOString().slice(0, 19).replace('T', ' ')}' WHERE mold_id = '${mold}'`;
            const updateCavityQuery = `UPDATE cavity SET availability_date = '${endDate.toISOString().slice(0, 19).replace('T', ' ')}' WHERE cavity_id = '${cavity}'`;
            const updateTireQuery = `UPDATE tire SET availability_date = '${endDate.toISOString().slice(0, 19).replace('T', ' ')}' WHERE icode = '${icode}'`;

            conn.query(updateMoldQuery, (err) => {
              if (err) {
                console.error('Query failed:', err);
              }
            });

            conn.query(updateCavityQuery, (err) => {
              if (err) {
                console.error('Query failed:', err);
              }
            });

            conn.query(updateTireQuery, (err) => {
              if (err) {
                console.error('Query failed:', err);
              }
            });
          });
        });

        console.log('Production plan generated successfully!');
      } else {
        console.log('No tires found in the database.');
      }

      // Close the database connection
      conn.end();
    });
  });
});
