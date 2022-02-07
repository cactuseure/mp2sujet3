<?php 
require 'Formation.inc.php';

class MySQL
{
	private static $instance = null; //mémorisation de l'instance de DB pour appliquer le pattern Singleton
	private $connect=null;           //connexion PDO à la base

	/************************************************************************/
	/*        Constructeur gerant  la connexion à la base via PDO           */
	/************************************************************************/
	private function __construct()
	{
		// Connexion à la base de données
		$connStr = 'mysql:host=database;dbname=mp2_sujet3';
		try
		{
			// Connexion à la base
			$this->connect = new PDO($connStr, 'root', 'tiger');
			// Configuration facultative de la connexion
			$this->connect->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			$this->connect->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			echo "probleme de connexion :".$e->getMessage();
			return null;
		}
	}

	/************************************************************************/
	/*          Methode permettant d'obtenir un objet instance de DB        */
	/************************************************************************/
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			try { self::$instance = new MySQL(); }
			catch (PDOException $e) { echo $e; }
		}

		$obj = self::$instance;

		if (($obj->connect) == null) self::$instance=null;
		return self::$instance;
	}

	/************************************************************************/
	/*    Methode permettant de fermer la connexion a la base de données    */
	/************************************************************************/
	public function close() { $this->connect = null; }

	/************************************************************************/
	/*    Methode uniquement utilisable dans les méthodes de la class DB.   */
	/*     Permet d'exécuter n'importe quelle requête SQL et renvoit les    */
	/*    tuples renvoyés par la requête sous forme d'un tableau d'objets   */ 
	/************************************************************************/
	/* param1 : texte de la requête à exécuter (éventuellement paramétrée)  */
	/* param2 : tableau des valeurs permettant d'instancier les paramètres  */
	/* param3 : nom de la classe devant être utilisée pour créer les objets */
	/* qui vont représenter les différents tuples.                          */
	/************************************************************************/
	private function execQuery($requete,$tparam,$nomClasse)
	{
		//on prépare la requête
		$stmt = $this->connect->prepare($requete);
		//on indique que l'on va récupére les tuples sous forme d'objets instance de Client
		$stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $nomClasse); 
		//on exécute la requête

		if ($tparam != null)
        {
            $stmt->execute($tparam);
        }
		else 
        {
            $stmt->execute();
        }

		//récupération du résultat de la requête sous forme d'un tableau d'objets
		$tab = array();
		$tuple = $stmt->fetch(); //on récupère le premier tuple sous forme d'objet
		if ($tuple)
		{
			//au moins un tuple a été renvoyé
			while ($tuple != false)
			{
				$tab[]=$tuple;           //on ajoute l'objet en fin de tableau
				$tuple = $stmt->fetch(); //on récupère un tuple sous la forme
				                         //d'un objet instance de la classe $nomClasse
			}
		}
		return $tab;
	}

	/************************************************************************/
	/*   Methode utilisable uniquement dans les méthodes de la classe DB.   */
	/*   permet d'exécuter n'importe quel ordre SQL autre qu'une requête.   */
	/*  Résultat : nombre de tuples affectés par l'exécution de l'ordre SQL */
	/************************************************************************/
	/*  param1 : texte de l'ordre SQL à exécuter (éventuellement paramétré) */
	/*  param2 : tableau des valeurs permettant d'instancier les paramètres */
	/************************************************************************/
	private function execMaj($ordreSQL,$tparam)
	{		
		$stmt = $this->connect->prepare($ordreSQL);
		$res = $stmt->execute($tparam); //execution de l'ordre SQL 
		echo($stmt->rowCount());
		return $stmt->rowCount();
	}

	/************************************************************************/
	/*      Fonctions qui peuvent être utilisées dans les scripts PHP       */
	/************************************************************************/

    public function getFormations()
	{
		$requete = 'SELECT * FROM `formation`';
		return $this->execQuery($requete,null,'Formation');
	}

    public function getFormation($url)
    {
        $requete = 'SELECT * FROM formation WHERE titre_url = ?';
        return $this->execQuery($requete, array($url), 'Formation');
    }
}

/* 


    // Récupère tous les utilisateurs et retourne un tableau d'objets Utilisateur les contenant.
    public function getUtilisateurs()
    {
        $requete = 'SELECT * FROM filiere.utilisateur';
        return $this->execQuery($requete, null, 'Utilisateur');
    }

    // Récupère un utilisateur à l'aide de son email et retourne un tableau d'objets Utilisateur le contenant.
    public function getUtilisateur($email)
    {
        $requete = 'SELECT * FROM filiere.utilisateur WHERE email = ?';
        return $this->execQuery($requete, array($email), 'Utilisateur');
    }

    public function getUtilisateurById($id)
    {
        $requete = 'SELECT * FROM filiere.utilisateur WHERE id_utilisateur = ?';
        return $this->execQuery($requete, array($id), 'Utilisateur');
    }

    // Récupère les matieres liées à l'utilisateur dont l'adresse email est passée en paramètre 
    // et retourne un tableau d'objets Matiere les contenant.
    public function getMatieresUtilisateur($email)
    {
        $requete = 'SELECT * FROM filiere.matiere NATURAL JOIN filiere.utilisateur_enseigne
					WHERE id_utilisateur IN (SELECT id_utilisateur FROM filiere.utilisateur WHERE email = ?)';
        return $this->execQuery($requete, array($email), 'Matiere');
    }

    // Insère un utilisateur dans la base, certaines données peuvent être null ou vide.
    public function insertUtilisateur($nom, $pwd, $pnom, $mail, $rgt, $tel, $src, $lkd, $lien, $dpl, $sts)
    {
        $requete = 'INSERT INTO filiere.utilisateur VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $tparam = array(0, $nom, $pwd, $pnom, $mail, $rgt, "", $tel, $src, $lkd, $lien, $dpl, $sts);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le mot de passe de l'utilisateur dont l'email est passé en paramètre.
    public function updateMotDePasse($email, $pwd)
    {
        $requete = 'UPDATE filiere.utilisateur SET mdp = ? WHERE email = ?';
        $tparam = array($pwd, $email);
        return $this->execMaj($requete, $tparam);
    }

    public function updateMotDePasseById($id, $pwd)
    {
        $requete = 'UPDATE filiere.utilisateur SET mdp = ? WHERE id_utilisateur = ?';
        $tparam = array($pwd, $id);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le nom de l'utilisateur dont l'email est passé en paramètre.
    public function updateNom($email, $nom)
    {
        $requete = 'UPDATE filiere.utilisateur SET nom = ? WHERE email = ?';
        $tparam = array($nom, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le prénom de l'utilisateur dont l'email est passé en paramètre.
    public function updatePrenom($email, $pnom)
    {
        $requete = 'UPDATE filiere.utilisateur SET prenom = ? WHERE email = ?';
        $tparam = array($pnom, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour l'email de l'utilisateur dont l'email est passé en paramètre.
    public function updateEmail($oldMail, $newMail)
    {
        $requete = 'UPDATE filiere.utilisateur SET email = ? WHERE email = ?';
        $tparam = array($newMail, $oldMail);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le niveau de droit de l'utilisateur dont l'email est passé en paramètre.
    public function updatePrivilege($email, $rgt)
    {
        $requete = 'UPDATE filiere.utilisateur SET privilege = ? WHERE email = ?';
        $tparam = array($rgt, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la biographie de l'utilisateur dont l'email est passé en paramètre.
    public function updateBiographie($email, $bio)
    {
        $requete = 'UPDATE  filiere.utilisateur SET biographie = ? WHERE email = ?';
        $tparam = array($bio, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le numéro de téléphone de l'utilisateur dont l'email est passé en paramètre.
    public function updateTelephone($email, $tel)
    {
        $requete = 'UPDATE filiere.utilisateur SET telephone = ? WHERE email = ?';
        $tparam = array($tel, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la source de la photo de l'utilisateur dont l'email est passé en paramètre.
    public function updatePhoto($email, $src)
    {
        $requete = 'UPDATE filiere.utilisateur SET photo = ? WHERE email = ?';
        $tparam = array($src, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le lien linkedin de l'utilisateur dont l'email est passé en paramètre.
    public function updateLinkedin($email, $lkd)
    {
        $requete = 'UPDATE filiere.utilisateur SET linkedin = ? WHERE email = ?';
        $tparam = array($lkd, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour un autre lien choisi par l'utilisateur dont l'email est passé en paramètre.
    public function updateAutreLien($email, $lien)
    {
        $requete = 'UPDATE filiere.utilisateur SET autreLien = ? WHERE email = ?';
        $tparam = array($lien, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le ou les diplômes de l'utilisateur dont l'email est passé en paramètre.
    public function updateDiplome($email, $dpl)
    {
        $requete = 'UPDATE filiere.utilisateur SET diplome = ? WHERE email = ?';
        $tparam = array($dpl, $email);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le status d'enseignant de l'utilisateur dont l'email est passé en paramètre.
    public function updateStatus($email, $sts)
    {
        $requete = 'UPDATE filiere.utilisateur SET status = ? WHERE email = ?';
        $tparam = array($sts, $email);
        return $this->execMaj($requete, $tparam);
    }
    // Met à jour le profil de l'utilisateur en fonction de son id.
    public function updateProfil($pwd, $nom, $pnom, $mail, $bio, $tel, $src, $lkd, $lien, $dpl, $sts, $idU)
    {
        $requete = 'UPDATE filiere.utilisateur SET `mdp`=?,`nom`=?,`prenom`=?,`email`=?,`biographie`=?,`telephone`=?,`photo`=?,`linkedin`=?,`autrelien`=?,`diplome`=?,`status`=? WHERE `id_utilisateur`=?';
        $tparam = array($pwd, $nom, $pnom, $mail, $bio, $tel, $src, $lkd, $lien, $dpl, $sts, $idU);
        return $this->execMaj($requete, $tparam);
    }
    public function updateProfilParAdmin($nom, $pnom, $mail, $privilege, $idU)
    {
        $requete = 'UPDATE filiere.utilisateur SET `nom`=?,`prenom`=?,`email`=?,`privilege`=? WHERE `id_utilisateur`=?';
        $tparam = array($nom, $pnom, $mail, $privilege, $idU);
        return $this->execMaj($requete, $tparam);
    }

    public function deleteUtilisateur($id)
    {
        $requete = 'DELETE FROM filiere.utilisateur WHERE id_utilisateur = ?';
        $tparam = array($id);
        return $this->execMaj($requete, $tparam);
    }

    // Récupère les formations d'un utilisateur
    public function getFormationsUtilisateur($idU)
    {
        $requete = 'SELECT * FROM formation f, utilisateur u, user_formation uf WHERE f.id_formation = uf.id_formation1 and u.id_utilisateur = uf.id_utilisateur2 and uf.id_utilisateur2 = ?';
        return $this->execQuery($requete, array($idU), 'Formation', 'Utilisateur');
    }

    // Récupère les formations auxquelles l'utilisateur ne participe pas
    public function getFormationsNonAttribuer($idU)
    {
        $requete = 'SELECT titre, id_formation from formation f where f.id_formation 
		NOT IN (SELECT id_formation1 from user_formation uf where uf.id_utilisateur2 = ?)';
        return $this->execQuery($requete, array($idU), 'Formation');
    }

    // Supprime de la table user_formation une formation de l'utilisateur
    public function DeleteFormationUtilisateur($idF, $idU)
    {
        $requete = 'DELETE  FROM user_formation  WHERE  user_formation.id_formation1 = ? and  user_formation.id_utilisateur2 = ?';
        $tparam = array($idF, $idU);
        return $this->execMaj($requete, $tparam);
    }




    // Récupère toutes les formations et retourne un tableau d'objets Formation les contenant.
    public function getFormations()
    {
        $requete = 'SELECT * FROM mp2_sujet3.formation';
        return $this->execQuery($requete, null, 'Formation');
    }

    // Récupère une formation à l'aide de son titre et retourne un tableau d'objets Formation la contenant.
    public function getFormation($titre)
    {
        $requete = 'SELECT * FROM filiere.formation WHERE titre = ?';
        return $this->execQuery($requete, array($titre), 'Formation');
    }



    // récupère les utilisateurs d'une formation
    public function getUtilisateursFormation($titreFormation)
    {
        $requete = 'SELECT utilisateur.nom, utilisateur.prenom, utilisateur.biographie, utilisateur.photo from utilisateur, formation f, user_formation uf where uf.id_utilisateur2 = utilisateur.id_utilisateur and   f.id_formation = uf.id_formation1 and f.titre = ?';
        return $this->execQuery($requete, array($titreFormation), 'Utilisateur');
    }

    public function insertFormationUtilisateur($idF, $idU)
    {
        $requete = 'INSERT INTO user_formation VALUES (?, ?, ?);';
        $tparam = array($idF, $idU, NULL);
        return $this->execMaj($requete, $tparam);
    }

    // Récupère tous les Utilisateur enseignants dans la formation dont le titre est passé en paramètre
    // et retourne un tableau d'objets Utilisateur les contenant.
    public function getUtilisateurEnseignants($titre)
    {
        $requete = 'SELECT * FROM filiere.utilisateur NATURAL JOIN filiere.enseignant_formation 
					WHERE id_formation IN (SELECT id_formation FROM filiere.formation WHERE titre = ?)';
        return $this->execQuery($requete, array($titre), 'Matiere');
    }

    // Récupère les matieres liées à la formation dont le titre est passé en paramètre 
    // et retourne un tableau d'objets Matiere les contenant.
    public function getMatieresFormation($titre)
    {
        $requete = 'SELECT * FROM filiere.matiere NATURAL JOIN filiere.matiere_enseignee 
					WHERE id_formation IN (SELECT id_formation FROM filiere.formation WHERE titre = ?)';
        return $this->execQuery($requete, array($titre), 'Matiere');
    }

    // Récupère un stage à l'aide de son id passé en paramètre et retourne un tableau d'objets Stage le contenant.
    // L'id peut être récupéré dans les attributs de la formation.
    public function getStage($idS)
    {
        $requete = 'SELECT * FROM filiere.stage WHERE id_stage = ?';
        return $this->execQuery($requete, array($idS), 'Stage');
    }

    // Récupère toutes les Stages  ou afficher = 1 et retourne un tableau d'objets stages les contenants pour les utilisateurs normaux.
    public function getStages($nomFormation)
    {
        $requete = 'SELECT * FROM filiere.formation INNER JOIN filiere.avis_stage ON (formation.id_formation = avis_stage.id_formation) where afficher =1 
		AND formation.titre = ?';
        $tparam = array($nomFormation);
        return $this->execQuery($requete, $tparam, 'Formation', 'Avisstage');
    }

    public function getStagesAdminAll()
    {
        $requete = 'SELECT * FROM filiere.formation INNER JOIN filiere.avis_stage ON (formation.id_formation = avis_stage.id_formation)';
        return $this->execQuery($requete, null, 'Formation', 'Avisstage');
    }
    //Récupère tout les temoignages pour l'admin (pour qu'il puisse valider)
    public function getStagesAdmin($nomFormation)
    {
        $requete = 'SELECT * FROM filiere.formation INNER JOIN filiere.avis_stage ON (formation.id_formation = avis_stage.id_formation) WHERE 
		formation.titre = ?';
        $tparam = array($nomFormation);
        return $this->execQuery($requete, $tparam, 'Formation', 'Avisstage');
    }

    // Insère une formation dans la base, certaines données peuvent être null ou vide.
    public function insertFormation($idF, $tlt, $tmp, $acc, $pres, $deb, $date, $type, $nomEtab, $idR, $red)
    {
        $requete = 'INSERT INTO filiere.formation VALUES(?,?,?,?,?,?,?,?,?,?,?)';
        $tparam = array(0, $tlt, $tmp, $acc, $pres, $deb, $date, $type, $nomEtab, $idR, $red);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le titre d'une formation dont le titre est passé en paramètre.
    public function updateTitre($oldTlt, $newTlt)
    {
        $requete = 'UPDATE filiere.formation SET titre = ? WHERE titre = ?';
        $tparam = array($newTlt, $oldTlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la durée d'une formation dont le titre est passé en paramètre.
    public function updateDuree($tlt, $duree)
    {
        $requete = 'UPDATE filiere.formation SET duree = ? WHERE titre = ?';
        $tparam = array($duree, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le niveau d'étude supérieure nécessère à l'accès à formation dont le titre est passé en paramètre.
    public function updateAcces($tlt, $acces)
    {
        $requete = 'UPDATE filiere.formation SET duree = ? WHERE titre = ?';
        $tparam = array($acces, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la présentation d'une formation dont le titre est passé en paramètre.
    public function updatePresentation($tlt, $pres)
    {
        $requete = 'UPDATE filiere.formation SET presentation = ? WHERE titre = ?';
        $tparam = array($pres, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour les debouchés d'une formation dont le titre est passé en paramètre.
    public function updateDebouches($tlt, $deb)
    {
        $requete = 'UPDATE filiere.formation SET debouches = ? WHERE titre = ?';
        $tparam = array($deb, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la date d'ajout d'une formation dont le titre est passé en paramètre.
    public function updateDateAjout($tlt, $date)
    {
        $requete = 'UPDATE filiere.formation SET date_ajout = ? WHERE titre = ?';
        $tparam = array($date, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le type d'une formation dont le titre est passé en paramètre.
    public function updateTypeFormation($tlt, $type)
    {
        $requete = 'UPDATE filiere.formation SET type_formation = ? WHERE titre = ?';
        $tparam = array($type, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le nom de l'établissement abritant la formation dont le titre est passé en paramètre.
    public function updateNomEtablissement($tlt, $netab)
    {
        $requete = 'UPDATE filiere.formation SET nom_etablissement = ? WHERE titre = ?';
        $tparam = array($netab, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le titre d'une formation dont le titre est passé en paramètre.
    public function updateResponsable($tlt, $idRes)
    {
        $requete = 'UPDATE filiere.formation SET id_responsable = ? WHERE titre = ?';
        $tparam = array($idRes, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour le titre d'une formation dont le titre est passé en paramètre.
    public function updateRedacteur($tlt, $idRed)
    {
        $requete = 'UPDATE filiere.formation SET id_redacteur = ? WHERE titre = ?';
        $tparam = array($idRed, $tlt);
        return $this->execMaj($requete, $tparam);
    }



    // Récupère toutes les actualités et retourne un tableau d'objets Actualite les contenant.
    public function getActus()
    {
        $requete = 'SELECT * FROM filiere.actualite';
        return $this->execQuery($requete, null, 'Actualite');
    }

    // Récupère une actualité dont le titre est passé en paramètre et retourne un tableau d'objets Actualite la contenant.
    public function getActuTitre($tlt)
    {
        $requete = 'SELECT * FROM filiere.actualite WHERE titre_actu = ?';
        return $this->execQuery($requete, array($tlt), 'Actualite');
    }

    // Récupère une actualité dont la date est passé en paramètre et retourne un tableau d'objets Actualite la contenant.
    public function getActuDate($date)
    {
        $requete = 'SELECT * FROM filiere.actualite WHERE date_actu = ?';
        return $this->execQuery($requete, array($date), 'Actualite');
    }

    // Met à jour le titre de l'actualité dont le titre est passé en paramètre.
    public function updateTitreActu($oldTlt, $newTlt)
    {
        $requete = 'UPDATE filiere.actualite SET titre_actu = ? WHERE titre_actu = ?';
        $tparam = array($newTlt, $oldTlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la description de l'actualité dont le titre est passé en paramètre.
    public function updateDescriptionActu($tlt, $desc)
    {
        $requete = 'UPDATE filiere.formation SET description = ? WHERE titre_actu = ?';
        $tparam = array($desc, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la date à laquelle l'actualité dont le titre est passé en paramètre se déroule.
    public function updateDateActu($tlt, $dtA)
    {
        $requete = 'UPDATE filiere.actualite SET date_actu = ? WHERE titre_actu = ?';
        $tparam = array($dtA, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    // Met à jour la date de parution de l'actualité dont le titre est passé en paramètre.
    public function updateDatePost($tlt, $dtP)
    {
        $requete = 'UPDATE filiere.formation SET id_redacteur = ? WHERE titre_actu = ?';
        $tparam = array($dtP, $tlt);
        return $this->execMaj($requete, $tparam);
    }

    /// Partie Témoignage 
    public function ajouterTemoignage($nom, $prenom, $formation, $avis)
    {
        $requete = 'INSERT INTO  filiere.temoignage (nom_tem, prenom_tem, forma_tem, avis_tem)
		Values (:nom, :prenom, :formation, :avis)';
        $tparam = array(
            'nom' => $nom,
            'prenom' => $prenom,
            'formation' => $formation,
            'avis' => $avis,
        );
        // $tparam = array($nom, $prenom, $formation, $avis);
        return $this->execMaj($requete, $tparam);
    }

    // Récupère toutes les Temoignages  ou afficher =1 et retourne un tableau d'objets témoignages les contenants pour les utilisateurs normaux.
    public function getTemoignages()
    {
        $requete = 'SELECT * FROM filiere.temoignage where afficher =1';
        return $this->execQuery($requete, null, 'Temoignage');
    }
    //Récupère tout les temoignages pour l'admin (pour qu'il puisse valider)
    public function getTemoignagesAdmin()
    {
        $requete = 'SELECT * FROM filiere.temoignage';
        return $this->execQuery($requete, null, 'Temoignage');
    }

    //Modifie le champ statut de la table témoignage 
    public function UpdateTemoignageStatut($statutTem, $idTem)
    {
        $requete = 'UPDATE filiere.temoignage SET statut = ? WHERE id_tem = ?';
        $tparam = array($statutTem, $idTem);
        return $this->execMaj($requete, $tparam);
    }
    // Modifie le champ afficher de la table temoignage
    public function UpdateTemoignageAfficher($afficherTem, $idTem)
    {
        $requete = 'UPDATE filiere.temoignage SET afficher = ? WHERE id_tem = ?';
        $tparam = array($afficherTem, $idTem);
        return $this->execMaj($requete, $tparam);
    }

    //Supprime un témoignage de la table
    public function DeleteTemoignage($idTem)
    {
        $requete = 'DELETE FROM filiere.temoignage WHERE id_tem = ?';
        $tparam = array($idTem);
        return $this->execMaj($requete, $tparam);
    }

    // Modifie le champ afficher de la table stage
    public function UpdateStageStatut($statutStage, $idStage)
    {
        $requete = 'UPDATE filiere.avis_stage SET statut = ? WHERE id_avis = ?';
        $tparam = array($statutStage, $idStage);
        return $this->execMaj($requete, $tparam);
    }

    // Modifie le champ afficher de la table stage
    public function UpdateStageAfficher($afficherStage, $idStage)
    {
        $requete = 'UPDATE filiere.avis_stage SET afficher = ? WHERE id_avis = ?';
        $tparam = array($afficherStage, $idStage);
        return $this->execMaj($requete, $tparam);
    }

    /// Partie Stage 
    public function ajouterStage($nom, $prenom, $nomEntr, $poste, $descr, $formation, $type)
    {
        $requete = 'INSERT INTO  filiere.avis_stage (nom_avis, prenom_avis, nom_entreprise,poste, description, id_formation, type) VALUES (?,?,?,?,?,?,?)';
        $tparam = array($nom, $prenom, $nomEntr, $poste, $descr, $formation, $type);
        return $this->execMaj($requete, $tparam);
    }

    //Supprime stage ou avis de la table
    public function DeleteStage($idStage)
    {
        $requete = 'DELETE FROM filiere.avis_stage WHERE id_avis = ?';
        $tparam = array($idStage);
        return $this->execMaj($requete, $tparam);
    }
}

*/
    
?>