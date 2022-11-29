DROP DATABASE IF EXISTS `colnet`;
CREATE DATABASE `colnet`;

USE `colnet`;
CREATE TABLE `etudiant` (
  `codePermanent` varchar(10) NOT NULL,
  `nomComplet` varchar(30) NOT NULL,
  `adresse` varchar(50) NOT NULL,
  `telephone` varchar(12) NOT NULL,
  `moyenne` double NOT NULL,
  `codeGroupe` varchar(7) NOT NULL
);


INSERT INTO `etudiant` (`codePermanent`, `nomComplet`, `adresse`, `telephone`, `moyenne`, `codeGroupe`) VALUES
('BERK110998', 'Kim Bergeron', '1300 Rue des Ursulines', '418-332-3985', 17.75, 'WEBA21L'),
('BERS031293', 'Sonia Bergeron', '500 Rue Saint-Jean', '418-999-1133', 18.5, 'WEBA21H'),
('BOUM091193', 'Mélanie Boutin', '1400 Rue Sherbrooke', '438-500-1265', 20, 'WEBA21C'),
('CREF031192', 'Franics Crevier', '22 Rue Sherbrooke', '514-479-5582', 7, 'WEBA21C'),
('DUFS230192', 'Simon Dufour', '15 Avenue de la Liberté', '514-998-1265', 8.5, 'WEBA21L'),
('FREJ221192', 'Johanne Frechette', '1300 Rue Labrecques', '418-122-4423', 8.5, 'WEBA21H'),
('HEWD231298', 'Danny Hewitt', '22 Rue des Forges', '514-222-3475', 20, 'WEBA21L'),
('LAMA041190', 'Alain Lamelin', '1800 Rue des Sentinelles', '418-554-1255', 11.5, 'WEBA21H'),
('PERG080294', 'Gilles Perrond', '20 Rue Saint-Denis', '438-599-7787', 12.25, 'WEBA21C'),
('PRAS261188', 'Samuel Pratte', '1400 Rue Hart', '514-431-3975', 18.75, 'WEBA21L'),
('SAVA091193', 'Alain Savoie', '20 Rue Simonne-Monet-Chartrand', '438-499-9987', 13, 'WEBA21C'),
('TURS091193', 'Simon Turmel', '1200 Rue Papineau', '418-399-1187', 19.5, 'WEBA21H');


CREATE TABLE `groupe` (
  `code` varchar(7) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `type` varchar(30) NOT NULL
);

INSERT INTO `groupe` (`code`, `nom`, `type`) VALUES
('WEBA21C', 'Techniques de développement web A21', 'En classe'),
('WEBA21H', 'Techniques de développement web A21', 'Hybride'),
('WEBA21L', 'Techniques de développement web A21', 'En ligne');


CREATE TABLE `utilisateur` (
  `id` int UNSIGNED NOT NULL,
  `nomComplet` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `codePostal` varchar(7) NOT NULL,
  `email` varchar(50) NOT NULL,
  `motDePasse` varchar(30) NOT NULL
);


ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`codePermanent`),
  ADD KEY `FK_codeGroupe` (`codeGroupe`);


ALTER TABLE `groupe`
  ADD PRIMARY KEY (`code`);


ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `utilisateur`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `etudiant`
  ADD CONSTRAINT `FK_codeGroupe` FOREIGN KEY (`codeGroupe`) REFERENCES `groupe` (`code`);
