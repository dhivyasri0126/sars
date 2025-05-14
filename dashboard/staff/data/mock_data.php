<?php
// Mock data for the staff dashboard
$mockData = [
    'students' => [
        ['id' => 1, 'name' => 'John Doe', 'grade' => 'A', 'activityCount' => 5, 'department' => 'Computer Science', 'year' => 2023],
        ['id' => 2, 'name' => 'Jane Smith', 'grade' => 'B+', 'activityCount' => 3, 'department' => 'Electrical Engineering', 'year' => 2023],
        ['id' => 3, 'name' => 'Mike Johnson', 'grade' => 'A-', 'activityCount' => 4, 'department' => 'Mechanical Engineering', 'year' => 2023],
        ['id' => 4, 'name' => 'Sarah Williams', 'grade' => 'B', 'activityCount' => 2, 'department' => 'Computer Science', 'year' => 2023],
        ['id' => 5, 'name' => 'David Brown', 'grade' => 'A+', 'activityCount' => 6, 'department' => 'Electrical Engineering', 'year' => 2023]
    ],

    'activities' => [
        ['id' => 1, 'studentId' => 1, 'title' => 'Project Submission', 'status' => 'approved', 'date' => '2024-03-15'],
        ['id' => 2, 'studentId' => 2, 'title' => 'Internship Report', 'status' => 'pending', 'date' => '2024-03-14'],
        ['id' => 3, 'studentId' => 3, 'title' => 'Workshop Attendance', 'status' => 'rejected', 'date' => '2024-03-13'],
        ['id' => 4, 'studentId' => 4, 'title' => 'Research Paper', 'status' => 'pending', 'date' => '2024-03-12'],
        ['id' => 5, 'studentId' => 5, 'title' => 'Conference Presentation', 'status' => 'approved', 'date' => '2024-03-11']
    ],

    'staffProfile' => [
        'id' => 1,
        'name' => 'Dr. Robert Wilson',
        'email' => 'robert.wilson@university.edu',
        'role' => 'Professor',
        'department' => 'Computer Science',
        'designation' => 'Head of Department',
        'joinDate' => '2020-01-15'
    ],

    'statistics' => [
        'totalStudents' => 5,
        'pendingApprovals' => 2,
        'totalActivities' => 5,
        'performanceData' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [65, 59, 80, 81, 56, 55]
        ]
    ]
];

// Functions to update data
function updateActivityStatus($activityId, $newStatus) {
    global $mockData;
    foreach ($mockData['activities'] as &$activity) {
        if ($activity['id'] === $activityId) {
            $activity['status'] = $newStatus;
            break;
        }
    }
}

function getStudentActivities($studentId) {
    global $mockData;
    return array_filter($mockData['activities'], function($activity) use ($studentId) {
        return $activity['studentId'] === $studentId;
    });
}

function getPendingApprovals() {
    global $mockData;
    return array_filter($mockData['activities'], function($activity) {
        return $activity['status'] === 'pending';
    });
}
?> 