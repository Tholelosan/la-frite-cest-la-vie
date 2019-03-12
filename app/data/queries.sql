INSERT INTO `personne` (`id`, `name`, `gender_id`)
VALUES (1, 'Fabrice', 1), (2, 'Maria', 2), (3, 'Clement', 1), (4, 'Thomas', 1), (5, 'Maxime', 1), (6, 'Maeva', 2), (7, 'Charles', 1);

INSERT INTO `sexe` (`id`, `name`)
VALUES (1, 'garçon'), (2 , 'fille');


-- depuis la table personne, s'il te plait, affiche moi le nom et le sexe. 

-- Selectionne les garçons dont le prénom commence par la lettre T ET je veux avoir en résultat de la requête, toute la table personne et le nom du sexe sans l'id du sexe

SELECT personne.name
FROM personne 
LIKE "T%"
INNER JOIN sexe ON personne.gender_id = sexe.id;

-- Juste le nom de Maeva et Maria

SELECT personne.name, sexe.name
FROM personne 
INNER JOIN sexe ON personne.gender_id = sexe.id;



-- mot de passe PHPMySQL : Ereul9Aeng

-- Scttpr