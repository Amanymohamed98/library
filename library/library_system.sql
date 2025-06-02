-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 09:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `BookID` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Author` varchar(100) DEFAULT NULL,
  `Publisher` varchar(100) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL CHECK (`Year` >= 1900),
  `ISBN` varchar(20) DEFAULT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  `Available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`BookID`, `Title`, `Author`, `Publisher`, `Year`, `ISBN`, `CategoryID`, `Available`) VALUES
(0, 'data science', 'am', 'A.m', 2001, '123', 1, 1),
(101, 'Physics Basics', 'Dr. John', 'EduPress', 2020, '978-1234567890', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CategoryID`, `CategoryName`) VALUES
(1, 'Science');

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `FineID` int(11) NOT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `Amount` decimal(5,2) DEFAULT NULL CHECK (`Amount` >= 0),
  `Reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine`
--

INSERT INTO `fine` (`FineID`, `StudentID`, `Amount`, `Reason`) VALUES
(1, 1, 2.00, 'Late return');

-- --------------------------------------------------------

--
-- Table structure for table `librarian`
--

CREATE TABLE `librarian` (
  `LibrarianID` int(11) NOT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian`
--

INSERT INTO `librarian` (`LibrarianID`, `FirstName`, `LastName`, `Email`) VALUES
(1, 'Fatma', 'Nasser', 'fatma.nasser@example.com'),
(2, 'أمين', 'جديد', 'new.librarian@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `loan`
--

CREATE TABLE `loan` (
  `LoanID` int(11) NOT NULL,
  `BookID` int(11) DEFAULT NULL,
  `StudentID` int(11) DEFAULT NULL,
  `LibrarianID` int(11) DEFAULT NULL,
  `LoanDate` date NOT NULL,
  `DueDate` date NOT NULL,
  `ReturnStatus` enum('Returned','Overdue','Borrowed') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan`
--

INSERT INTO `loan` (`LoanID`, `BookID`, `StudentID`, `LibrarianID`, `LoanDate`, `DueDate`, `ReturnStatus`) VALUES
(1, 0, 0, NULL, '2025-05-19', '2025-06-02', 'Borrowed'),
(2, 101, 1, NULL, '2025-05-19', '2025-06-02', 'Borrowed'),
(3, 101, 0, 1, '2025-05-19', '2025-06-02', 'Borrowed'),
(4, 0, 0, 1, '2025-05-19', '2025-06-02', 'Borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `return`
--

CREATE TABLE `return` (
  `ReturnID` int(11) NOT NULL,
  `LoanID` int(11) DEFAULT NULL,
  `ReturnDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return`
--

INSERT INTO `return` (`ReturnID`, `LoanID`, `ReturnDate`) VALUES
(0, 0, '2025-05-19'),
(1, 1, '2025-05-10');

-- --------------------------------------------------------

--
-- Table structure for table `return_temp`
--

CREATE TABLE `return_temp` (
  `ReturnID` int(11) NOT NULL,
  `LoanID` int(11) DEFAULT NULL,
  `ReturnDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_temp`
--

INSERT INTO `return_temp` (`ReturnID`, `LoanID`, `ReturnDate`) VALUES
(1, 1, '2025-05-10');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Class` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`StudentID`, `FirstName`, `LastName`, `Class`, `Email`) VALUES
(0, 'rawan', 'mohamed', '10A', 'Rawan@gmail.com'),
(1, 'Ali', 'Salim', '10A', 'ali.salim@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `useraccount`
--

CREATE TABLE `useraccount` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Role` varchar(20) DEFAULT NULL CHECK (`Role` in ('Student','Librarian')),
  `StudentID` int(11) DEFAULT NULL,
  `LibrarianID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `useraccount`
--

INSERT INTO `useraccount` (`UserID`, `Username`, `Password`, `Role`, `StudentID`, `LibrarianID`) VALUES
(0, 'Rawan', '$2y$10$Ls7QZB.NOzTKkSg/ErPZC.xz0s03MhU6imeW5VfzBUyFLJQQQjooy', 'Student', 0, NULL),
(1, 'ali123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Student', 1, NULL),
(2, 'fatma_lib', 'admin123', 'Librarian', NULL, 1),
(3, 'admin', '$2y$10$yEyubh2o7dL7wctaI6U8gubS/K/hqxe0jgkQ8dRpfUfgXCD9zP6ZC', 'Librarian', NULL, 2),
(4, 'admin5', 'admin1234', 'Librarian', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`BookID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`FineID`),
  ADD KEY `StudentID` (`StudentID`);

--
-- Indexes for table `librarian`
--
ALTER TABLE `librarian`
  ADD PRIMARY KEY (`LibrarianID`);

--
-- Indexes for table `loan`
--
ALTER TABLE `loan`
  ADD PRIMARY KEY (`LoanID`),
  ADD KEY `BookID` (`BookID`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `LibrarianID` (`LibrarianID`);

--
-- Indexes for table `return`
--
ALTER TABLE `return`
  ADD PRIMARY KEY (`ReturnID`),
  ADD KEY `LoanID` (`LoanID`);

--
-- Indexes for table `return_temp`
--
ALTER TABLE `return_temp`
  ADD PRIMARY KEY (`ReturnID`),
  ADD KEY `LoanID` (`LoanID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`StudentID`);

--
-- Indexes for table `useraccount`
--
ALTER TABLE `useraccount`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `LibrarianID` (`LibrarianID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `loan`
--
ALTER TABLE `loan`
  MODIFY `LoanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `category` (`CategoryID`);

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`);

--
-- Constraints for table `loan`
--
ALTER TABLE `loan`
  ADD CONSTRAINT `loan_ibfk_1` FOREIGN KEY (`BookID`) REFERENCES `book` (`BookID`),
  ADD CONSTRAINT `loan_ibfk_2` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`),
  ADD CONSTRAINT `loan_ibfk_3` FOREIGN KEY (`LibrarianID`) REFERENCES `librarian` (`LibrarianID`);

--
-- Constraints for table `useraccount`
--
ALTER TABLE `useraccount`
  ADD CONSTRAINT `useraccount_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`),
  ADD CONSTRAINT `useraccount_ibfk_2` FOREIGN KEY (`LibrarianID`) REFERENCES `librarian` (`LibrarianID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
