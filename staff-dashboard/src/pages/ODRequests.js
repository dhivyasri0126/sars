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
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
} from '@mui/material';
import { useAuth } from '../contexts/AuthContext';
import * as XLSX from 'xlsx';

const ODRequests = () => {
  const { user } = useAuth();
  const [selectedRequest, setSelectedRequest] = useState(null);
  const [approvalDialog, setApprovalDialog] = useState(false);
  const [approvalComment, setApprovalComment] = useState('');

  // Mock data
  const odRequests = [
    {
      id: 1,
      studentName: 'John Doe',
      eventName: 'Hackathon 2023',
      eventType: 'Technical',
      department: 'cse',
      batch: '2021',
      section: 'A',
      eventDate: '2023-05-15',
      tutorApproval: 'Approved',
      advisorApproval: 'Pending',
      hodApproval: 'Pending',
      certificate: null,
      status: 'Tutor Approved',
    },
    {
      id: 2,
      studentName: 'Jane Smith',
      eventName: 'Cultural Fest',
      eventType: 'Non-Technical',
      department: 'ece',
      batch: '2022',
      section: 'B',
      eventDate: '2023-05-20',
      tutorApproval: 'Approved',
      advisorApproval: 'Approved',
      hodApproval: 'Pending',
      certificate: null,
      status: 'Advisor Approved',
    },
    // Add more mock data as needed
  ];

  const handleApprove = (request) => {
    setSelectedRequest(request);
    setApprovalDialog(true);
  };

  const handleCloseDialog = () => {
    setApprovalDialog(false);
    setApprovalComment('');
  };

  const handleSubmitApproval = () => {
    // Mock approval submission
    console.log('Approval submitted:', {
      requestId: selectedRequest.id,
      comment: approvalComment,
      role: user.role,
    });
    handleCloseDialog();
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'Pending':
        return 'warning';
      case 'Approved':
        return 'success';
      case 'Rejected':
        return 'error';
      default:
        return 'default';
    }
  };

  const exportToExcel = () => {
    const ws = XLSX.utils.json_to_sheet(odRequests);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'OD Requests');
    XLSX.writeFile(wb, 'od_requests.xlsx');
  };

  const canApprove = (request) => {
    switch (user.role) {
      case 'tutor':
        return request.tutorApproval === 'Pending';
      case 'advisor':
        return request.tutorApproval === 'Approved' && request.advisorApproval === 'Pending';
      case 'hod':
        return request.advisorApproval === 'Approved' && request.hodApproval === 'Pending';
      default:
        return false;
    }
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        OD Requests
      </Typography>
      <Grid container spacing={3}>
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
                  <TableCell>Student Name</TableCell>
                  <TableCell>Event Name</TableCell>
                  <TableCell>Event Type</TableCell>
                  <TableCell>Department</TableCell>
                  <TableCell>Batch</TableCell>
                  <TableCell>Section</TableCell>
                  <TableCell>Event Date</TableCell>
                  <TableCell>Tutor Approval</TableCell>
                  <TableCell>Advisor Approval</TableCell>
                  <TableCell>HOD Approval</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell>Action</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {odRequests.map((request) => (
                  <TableRow key={request.id}>
                    <TableCell>{request.studentName}</TableCell>
                    <TableCell>{request.eventName}</TableCell>
                    <TableCell>{request.eventType}</TableCell>
                    <TableCell>{request.department.toUpperCase()}</TableCell>
                    <TableCell>{request.batch}</TableCell>
                    <TableCell>{request.section}</TableCell>
                    <TableCell>{request.eventDate}</TableCell>
                    <TableCell>
                      <Chip
                        label={request.tutorApproval}
                        color={getStatusColor(request.tutorApproval)}
                        size="small"
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={request.advisorApproval}
                        color={getStatusColor(request.advisorApproval)}
                        size="small"
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={request.hodApproval}
                        color={getStatusColor(request.hodApproval)}
                        size="small"
                      />
                    </TableCell>
                    <TableCell>{request.status}</TableCell>
                    <TableCell>
                      {canApprove(request) && (
                        <Button
                          variant="contained"
                          color="primary"
                          size="small"
                          onClick={() => handleApprove(request)}
                        >
                          Approve
                        </Button>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </Grid>
      </Grid>

      <Dialog open={approvalDialog} onClose={handleCloseDialog}>
        <DialogTitle>Approve OD Request</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Comment"
            fullWidth
            multiline
            rows={4}
            value={approvalComment}
            onChange={(e) => setApprovalComment(e.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Cancel</Button>
          <Button onClick={handleSubmitApproval} variant="contained" color="primary">
            Submit Approval
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default ODRequests; 