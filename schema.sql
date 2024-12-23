

--
-- Database: `thecrux`
--
CREATE Database thecrux;
USE thecrux;
-- --------------------------------------------------------

--
-- Table structure for table `climb`
--

CREATE TABLE `climb` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `grade` varchar(3) NOT NULL,
  `climbed` tinyint(1) NOT NULL,
  `route` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`route`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

--
-- Dumping data for table `climb`
--

INSERT INTO `climb` (`id`, `name`, `grade`, `climbed`, `route`) VALUES
(1, 'Felsenflucht', '6a', 1, '[[true,false,false,false],[false,true,false,true],[false,false,false,true],[false,true,false,false],[true,false,false,true],[false,true,true,false]]'),
(2, 'Gravitationskrieger', '5+', 0, '[[true,false,true,false],[false,false,true,false],[true,false,false,false],[true,false,false,false],[false,true,false,false],[false,true,false,true]]'),
(3, 'Felsgeister', '5+', 0, '[[false,true,true,false],[false,true,false,false],[false,false,true,false],[true,false,false,true],[false,false,true,false],[false,true,false,false]]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `climb`
--
ALTER TABLE `climb`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `climb`
--
ALTER TABLE `climb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
