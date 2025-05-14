import React, { useState } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
} from '@mui/material';
import { Close as CloseIcon } from '@mui/icons-material';
import { useAuth } from '../contexts/AuthContext';
import * as XLSX from 'xlsx';

const Activities = () => {
  const { user } = useAuth();
  const [filters, setFilters] = useState({
    department: '',
    batch: '',
    section: '',
    odStatus: '',
    eventType: '',
  });
  const [selectedCertificate, setSelectedCertificate] = useState(null);

  // Mock data
  const activities = [
    {
      id: 1,
      eventName: 'Hackathon 2023',
      eventType: 'Technical',
      department: 'cse',
      batch: '2021',
      section: 'A',
      odStatus: 'Confirmed',
      certificate: 'hackathon_cert.pdf',
    },
    {
      id: 2,
      eventName: 'Cultural Fest',
      eventType: 'Non-Technical',
      department: 'ece',
      batch: '2022',
      section: 'B',
      odStatus: 'Pending',
      certificate: null,
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

  const handleCertificateClick = (certificate) => {
    setSelectedCertificate(certificate);
  };

  const handleCloseCertificate = () => {
    setSelectedCertificate(null);
  };

  const exportToExcel = () => {
    const filteredData = activities.filter((activity) => {
      return (
        (!filters.department || activity.department === filters.department) &&
        (!filters.batch || activity.batch === filters.batch) &&
        (!filters.section || activity.section === filters.section) &&
        (!filters.odStatus || activity.odStatus === filters.odStatus) &&
        (!filters.eventType || activity.eventType === filters.eventType)
      );
    });

    const ws = XLSX.utils.json_to_sheet(filteredData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Activities');
    XLSX.writeFile(wb, 'activities.xlsx');
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Activities
      </Typography>
      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Grid container spacing={2}>
              <Grid item xs={12} sm={6} md={2}>
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
              <Grid item xs={12} sm={6} md={2}>
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
              <Grid item xs={12} sm={6} md={2}>
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
              <Grid item xs={12} sm={6} md={2}>
                <FormControl fullWidth>
                  <InputLabel>OD Status</InputLabel>
                  <Select
                    name="odStatus"
                    value={filters.odStatus}
                    onChange={handleFilterChange}
                    label="OD Status"
                  >
                    <MenuItem value="">All</MenuItem>
                    <MenuItem value="Pending">Pending</MenuItem>
                    <MenuItem value="Confirmed">Confirmed</MenuItem>
                    <MenuItem value="Expired">Expired</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid item xs={12} sm={6} md={2}>
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
                    <MenuItem value="Both">Both</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid item xs={12} sm={6} md={2}>
                <Button
                  variant="contained"
                  color="primary"
                  fullWidth
                  onClick={exportToExcel}
                  sx={{ height: '56px' }}
                >
                  Export to Excel
                </Button>
              </Grid>
            </Grid>
          </Paper>
        </Grid>
        <Grid item xs={12}>
          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Event Name</TableCell>
                  <TableCell>Event Type</TableCell>
                  <TableCell>Department</TableCell>
                  <TableCell>Batch</TableCell>
                  <TableCell>Section</TableCell>
                  <TableCell>OD Status</TableCell>
                  <TableCell>Certificate</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {activities
                  .filter((activity) => {
                    return (
                      (!filters.department ||
                        activity.department === filters.department) &&
                      (!filters.batch || activity.batch === filters.batch) &&
                      (!filters.section ||
                        activity.section === filters.section) &&
                      (!filters.odStatus ||
                        activity.odStatus === filters.odStatus) &&
                      (!filters.eventType ||
                        activity.eventType === filters.eventType)
                    );
                  })
                  .map((activity) => (
                    <TableRow key={activity.id}>
                      <TableCell>{activity.eventName}</TableCell>
                      <TableCell>{activity.eventType}</TableCell>
                      <TableCell>{activity.department.toUpperCase()}</TableCell>
                      <TableCell>{activity.batch}</TableCell>
                      <TableCell>{activity.section}</TableCell>
                      <TableCell>{activity.odStatus}</TableCell>
                      <TableCell>
                        {activity.certificate ? (
                          <Button
                            variant="text"
                            onClick={() =>
                              handleCertificateClick(activity.certificate)
                            }
                          >
                            View Certificate
                          </Button>
                        ) : (
                          'No Certificate'
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
              </TableBody>
            </Table>
          </TableContainer>
        </Grid>
      </Grid>

      <Dialog
        open={Boolean(selectedCertificate)}
        onClose={handleCloseCertificate}
        maxWidth="md"
        fullWidth
      >
        <DialogTitle>
          Certificate Preview
          <IconButton
            aria-label="close"
            onClick={handleCloseCertificate}
            sx={{ position: 'absolute', right: 8, top: 8 }}
          >
            <CloseIcon />
          </IconButton>
        </DialogTitle>
        <DialogContent>
          <Box
            component="img"
            src={`/certificates/${selectedCertificate}`}
            alt="Certificate"
            sx={{ width: '100%', height: 'auto' }}
          />
        </DialogContent>
      </Dialog>
    </Box>
  );
};

export default Activities; 