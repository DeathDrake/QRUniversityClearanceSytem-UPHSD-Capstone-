import React, { useEffect, useState } from 'react';
import { Pie } from 'react-chartjs-2';

const PieChart = () => {
  const [data, setData] = useState({ enrolled: 0, total: 0 });

  // Fetch data from your PHP backend
  useEffect(() => {
    fetch('/api/student-enrollment.php')
      .then((response) => response.json())
      .then((result) => setData(result))
      .catch((error) => console.error('Error fetching data:', error));
  }, []);

  // Prepare data for the chart
  const chartData = {
    labels: ['Enrolled Students', 'Non-Enrolled Students'],
    datasets: [
      {
        data: [data.enrolled, data.total - data.enrolled],
        backgroundColor: ['#4CAF50', '#FFC107'],
      },
    ],
  };

  return (
    <div>
      <h2>Student Enrollment Overview</h2>
      <Pie data={chartData} />
    </div>
  );
};

export default PieChart;
