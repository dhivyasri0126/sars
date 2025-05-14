import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Container,
  Paper,
  TextField,
  Button,
  Typography,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  FormControlLabel,
  Checkbox,
  Box,
} from '@mui/material';
import { useAuth } from '../contexts/AuthContext';

const Login = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    role: '',
    department: '',
    batch: '',
    section: '',
    rememberMe: false,
  });

  const handleChange = (e) => {
    const { name, value, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === 'rememberMe' ? checked : value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Mock login - in real app, this would be an API call
    const userData = {
      ...formData,
      id: 1,
      name: 'John Doe',
    };
    login(userData);
    navigate('/dashboard');
  };

  return (
    <Container component="main" maxWidth="xs">
      <Box
        sx={{
          marginTop: 8,
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
        }}
      >
        <Paper elevation={3} sx={{ p: 4, width: '100%' }}>
          <Typography component="h1" variant="h5" align="center" gutterBottom>
            Staff Login
          </Typography>
          <form onSubmit={handleSubmit}>
            <TextField
              margin="normal"
              required
              fullWidth
              label="Email Address"
              name="email"
              autoComplete="email"
              autoFocus
              value={formData.email}
              onChange={handleChange}
            />
            <TextField
              margin="normal"
              required
              fullWidth
              name="password"
              label="Password"
              type="password"
              autoComplete="current-password"
              value={formData.password}
              onChange={handleChange}
            />
            <FormControl fullWidth margin="normal">
              <InputLabel>Role</InputLabel>
              <Select
                name="role"
                value={formData.role}
                onChange={handleChange}
                label="Role"
              >
                <MenuItem value="tutor">Tutor</MenuItem>
                <MenuItem value="advisor">Advisor</MenuItem>
                <MenuItem value="hod">HOD</MenuItem>
                <MenuItem value="admin">Admin</MenuItem>
              </Select>
            </FormControl>
            {formData.role && formData.role !== 'admin' && (
              <>
                <FormControl fullWidth margin="normal">
                  <InputLabel>Department</InputLabel>
                  <Select
                    name="department"
                    value={formData.department}
                    onChange={handleChange}
                    label="Department"
                  >
                    <MenuItem value="cse">Computer Science</MenuItem>
                    <MenuItem value="ece">Electronics</MenuItem>
                    <MenuItem value="mech">Mechanical</MenuItem>
                  </Select>
                </FormControl>
                <FormControl fullWidth margin="normal">
                  <InputLabel>Batch</InputLabel>
                  <Select
                    name="batch"
                    value={formData.batch}
                    onChange={handleChange}
                    label="Batch"
                  >
                    <MenuItem value="2020">2020</MenuItem>
                    <MenuItem value="2021">2021</MenuItem>
                    <MenuItem value="2022">2022</MenuItem>
                  </Select>
                </FormControl>
                <FormControl fullWidth margin="normal">
                  <InputLabel>Section</InputLabel>
                  <Select
                    name="section"
                    value={formData.section}
                    onChange={handleChange}
                    label="Section"
                  >
                    <MenuItem value="A">A</MenuItem>
                    <MenuItem value="B">B</MenuItem>
                    <MenuItem value="C">C</MenuItem>
                  </Select>
                </FormControl>
              </>
            )}
            <FormControlLabel
              control={
                <Checkbox
                  name="rememberMe"
                  checked={formData.rememberMe}
                  onChange={handleChange}
                  color="primary"
                />
              }
              label="Remember me"
            />
            <Button
              type="submit"
              fullWidth
              variant="contained"
              sx={{ mt: 3, mb: 2 }}
            >
              Sign In
            </Button>
          </form>
        </Paper>
      </Box>
    </Container>
  );
};

export default Login; 