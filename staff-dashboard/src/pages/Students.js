import React, { useState } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  TextField,
} from '@mui/material';
import { useAuth } from '../contexts/AuthContext';
import * as XLSX from 'xlsx';

const Students = () => {
  const { user, hasDepartmentAccess } = useAuth();
  const [filters, setFilters] = useState({
    department: '',
    batch: '',
    section: '',
    search: '',
    eventType: '',
  });

  // Mock data
  const students = [
    {
      id: 1,
      name: 'John Doe',
      rollNumber: 'CSE2021001',
      department: 'cse',
      batch: '2021',
      section: 'A',
      eventType: 'Technical',
      email: 'john.doe@example.com',
      phone: '1234567890',
    },
    {
      id: 2,
      name: 'Jane Smith',
      rollNumber: 'ECE2022001',
      department: 'ece',
      batch: '2022',
      section: 'B',
      eventType: 'Non-Technical',
      email: 'jane.smith@example.com',
      phone: '9876543210',
    },
    // Add more mock data as needed
  ];

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const exportToExcel = () => {
    const filteredData = students.filter((student) => {
      return (
        (!filters.department || student.department === filters.department) &&
        (!filters.batch || student.batch === filters.batch) &&
        (!filters.section || student.section === filters.section) &&
        (!filters.search ||
          student.name.toLowerCase().includes(filters.search.toLowerCase()) ||
          student.rollNumber.toLowerCase().includes(filters.search.toLowerCase()))
      );
    });

    const ws = XLSX.utils.json_to_sheet(filteredData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Students');
    XLSX.writeFile(wb, 'students.xlsx');
  };

  const filteredStudents = students.filter((student) => {
    if (!hasDepartmentAccess(student.department)) return false;
    return (
      (!filters.department || student.department === filters.department) &&
      (!filters.batch || student.batch === filters.batch) &&
      (!filters.section || student.section === filters.section) &&
      (!filters.search ||
        student.name.toLowerCase().includes(filters.search.toLowerCase()) ||
        student.rollNumber.toLowerCase().includes(filters.search.toLowerCase()))
    );
  });

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Students
      </Typography>
      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Grid container spacing={2}>
              <Grid item xs={12} sm={6} md={3}>
                <FormControl fullWidth>
                  <InputLabel>Department</InputLabel>
                  <Select
                    name="department"
                    value={filters.department}
                    onChange={handleFilterChange}
                    label="Department"
                  >
                    <MenuItem value="">All</MenuItem>
                    <MenuItem value="cse">Computer Science</MenuItem>
                    <MenuItem value="ece">Electronics</MenuItem>
                    <MenuItem value="mech">Mechanical</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <FormControl fullWidth>
                  <InputLabel>Batch</InputLabel>
                  <Select
                    name="batch"
                    value={filters.batch}
                    onChange={handleFilterChange}
                    label="Batch"
                  >
                    <MenuItem value="">All</MenuItem>
                    <MenuItem value="2020">2020</MenuItem>
                    <MenuItem value="2021">2021</MenuItem>
                    <MenuItem value="2022">2022</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <FormControl fullWidth>
                  <InputLabel>Section</InputLabel>
                  <Select
                    name="section"
                    value={filters.section}
                    onChange={handleFilterChange}
                    label="Section"
                  >
                    <MenuItem value="">All</MenuItem>
                    <MenuItem value="A">A</MenuItem>
                    <MenuItem value="B">B</MenuItem>
                    <MenuItem value="C">C</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <TextField
                  fullWidth
                  label="Search"
                  name="search"
                  value={filters.search}
                  onChange={handleFilterChange}
                  placeholder="Search by name or roll number"
                />
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <FormControl fullWidth>
                  <InputLabel>Event Type</InputLabel>
                  <Select
                    name="eventType"
                    value={filters.eventType}
                    onChange={handleFilterChange}
                    label="Event Type"
                  >
                    <MenuItem value="">All</MenuItem>
                    <MenuItem value="Technical">Technical</MenuItem>
                    <MenuItem value="Non-Technical">Non-Technical</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
            </Grid>
          </Paper>
        </Grid>
        <Grid item xs={12}>
          <Button
            variant="contained"
            color="primary"
            onClick={exportToExcel}
            sx={{ mb: 2 }}
          >
            Export to Excel
          </Button>
          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Register Number</TableCell>
                  <TableCell>Name</TableCell>
                  <TableCell>Department</TableCell>
                  <TableCell>Batch</TableCell>
                  <TableCell>Section</TableCell>
                  <TableCell>Event Type</TableCell>
                  <TableCell>Email</TableCell>
                  <TableCell>Phone</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {filteredStudents.map((student) => (
                  <TableRow key={student.id}>
                    <TableCell>{student.rollNumber}</TableCell>
                    <TableCell>{student.name}</TableCell>
                    <TableCell>{student.department.toUpperCase()}</TableCell>
                    <TableCell>{student.batch}</TableCell>
                    <TableCell>{student.section}</TableCell>
                    <TableCell>{student.eventType}</TableCell>
                    <TableCell>{student.email}</TableCell>
                    <TableCell>{student.phone}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </Grid>
      </Grid>
    </Box>
  );
};

export default Students; 