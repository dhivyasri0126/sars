import React from 'react';
import {
  Grid,
  Paper,
  Typography,
  Box,
  Card,
  CardContent,
} from '@mui/material';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js';
import { Line, Bar, Pie } from 'react-chartjs-2';
import { useAuth } from '../contexts/AuthContext';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const Dashboard = () => {
  const { user } = useAuth();

  // Mock data for charts
  const odRequestsData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
      {
        label: 'OD Requests',
        data: [12, 19, 3, 5, 2, 3],
        borderColor: 'rgb(75, 192, 192)',
        tension: 0.1,
      },
    ],
  };

  const activitiesData = {
    labels: ['Technical', 'Non-Technical', 'Both'],
    datasets: [
      {
        data: [30, 50, 20],
        backgroundColor: [
          'rgb(255, 99, 132)',
          'rgb(54, 162, 235)',
          'rgb(255, 206, 86)',
        ],
      },
    ],
  };

  const approvalStatusData = {
    labels: ['Pending', 'Approved', 'Rejected'],
    datasets: [
      {
        label: 'OD Requests',
        data: [10, 20, 5],
        backgroundColor: [
          'rgb(255, 99, 132)',
          'rgb(75, 192, 192)',
          'rgb(255, 206, 86)',
        ],
      },
    ],
  };

  const stats = [
    {
      title: 'Total Students',
      value: '150',
      color: '#1976d2',
    },
    {
      title: 'Pending OD Requests',
      value: '12',
      color: '#dc004e',
    },
    {
      title: 'Approved OD Requests',
      value: '45',
      color: '#4caf50',
    },
    {
      title: 'Total Activities',
      value: '25',
      color: '#ff9800',
    },
  ];

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Welcome, {user?.name}
      </Typography>
      <Grid container spacing={3}>
        {stats.map((stat) => (
          <Grid item xs={12} sm={6} md={3} key={stat.title}>
            <Card>
              <CardContent>
                <Typography color="textSecondary" gutterBottom>
                  {stat.title}
                </Typography>
                <Typography variant="h4" component="div" sx={{ color: stat.color }}>
                  {stat.value}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
        ))}
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              OD Requests Trend
            </Typography>
            <Line data={odRequestsData} />
          </Paper>
        </Grid>
        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Activity Types
            </Typography>
            <Pie data={activitiesData} />
          </Paper>
        </Grid>
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              OD Request Status
            </Typography>
            <Bar data={approvalStatusData} />
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
};

export default Dashboard; 