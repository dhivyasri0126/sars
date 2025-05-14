# Staff Dashboard for Student Activity Record System

A modern, responsive dashboard for managing student activities and OD (On Duty) requests.

## Features

- Role-based access control (Tutor, Advisor, HOD, Admin)
- Department-based data filtering
- OD request approval workflow
- Certificate upload and preview
- Excel export functionality
- Responsive design
- Modern UI with Material-UI components

## Prerequisites

- Node.js (v14 or higher)
- npm (v6 or higher)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd staff-dashboard
```

2. Install dependencies:
```bash
npm install
```

3. Start the development server:
```bash
npm start
```

The application will be available at `http://localhost:3000`.

## Project Structure

```
staff-dashboard/
├── src/
│   ├── components/
│   │   └── Layout.js
│   ├── contexts/
│   │   └── AuthContext.js
│   ├── pages/
│   │   ├── Dashboard.js
│   │   ├── Activities.js
│   │   ├── ODRequests.js
│   │   ├── Students.js
│   │   └── Login.js
│   ├── App.js
│   ├── index.js
│   └── index.css
├── package.json
└── README.md
```

## Usage

1. Login with your credentials
2. Select your role and department
3. Navigate through the dashboard using the sidebar
4. Manage OD requests and view student activities
5. Export data to Excel as needed

## Development

- The application uses React with Material-UI for the frontend
- State management is handled through React Context
- Routing is managed by React Router
- Excel export is handled by the xlsx library
- Charts are created using Chart.js

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License. 