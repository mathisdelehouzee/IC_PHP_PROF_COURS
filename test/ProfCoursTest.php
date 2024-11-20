<?php
namespace Test;

use Cours;
use PHPUnit\Framework\TestCase;
use Prof;

class ProfCoursTest extends TestCase
{
    // Configuration de la base de données
    const FAKE_DBNAME = "##DB_NAME##";
    const SQL_FILE = "db.sql";
    const DB_USER = "user01";
    const DB_PASS = "user01";
    const DB_NAME = "user01_test_php";
    const DB_HOST = "192.168.250.3";

    public static $conn = null;

    // Attributs pour les tests
    private $prenom = "REVERGIE"; // a changer
    private $nom = "TATSUM"; // a changer
    private $date = "22/07/1984"; // a changer
    private $lieu = "Toulouse, France"; // a changer
    private $intitule = "Intégration continue"; //a remplir
    private $duree = "3h"; //a remplir

    private static $prof_a = [];
    private static $cours_a = [];

    /**
     * Préparation avant les tests
     * - Création de la base de données temporaire
     * - Initialisation des données nécessaires aux tests
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        echo __METHOD__ . "\n";

        try {
            if (file_exists(self::SQL_FILE)) {
                self::$conn = new \PDO('mysql:host=' . self::DB_HOST . ';charset=utf8', self::DB_USER, self::DB_PASS);
                self::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

                $sql_db = file_get_contents(self::SQL_FILE);
                $sql_db = str_replace(self::FAKE_DBNAME, self::DB_NAME, $sql_db);
                $sql_stmt = self::$conn->prepare($sql_db);

                if ($sql_stmt->execute()) {
                    print "Création de la base de données " . self::DB_NAME . " REUSSIE\n";
                    $sql_stmt->closeCursor();
                    self::$conn->query("USE " . self::DB_NAME . ";")->closeCursor();
                } else {
                    echo "Échec de la création de la base de données " . self::DB_NAME . "\n";
                }
            } else {
                throw new \Exception("Le fichier SQL " . self::SQL_FILE . " est inexistant.");
            }
        } catch (\Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        // Initialisation des données pour les tests
        self::$prof_a = [
            // Question 6 : Insérer les enregistrements suivants dans la table prof
            new Prof("Nom_prof3", "Prenom_prof3", "10/03/1982", "lieu_prof3"),
            new Prof("Nom_prof4", "Prenom_prof4", "10/04/1982", "lieu_prof4"),
            new Prof("Nom_prof5", "Prenom_prof5", "10/05/1982", "lieu_prof5"),
            new Prof("Nom_prof6", "Prenom_prof6", "10/06/1982", "lieu_prof6"),
            new Prof("Nom_prof7", "Prenom_prof7", "10/07/1982", "lieu_prof7"),
            new Prof("Nom_prof8", "Prenom_prof8", "10/08/1982", "lieu_prof8"),
            new Prof("Nom_prof9", "Prenom_prof9", "10/09/1982", "lieu_prof9"),
            new Prof("Nom_prof10", "Prenom_prof10", "10/10/1982", "lieu_prof10") // ** A MODIFIER **
        ];

        self::$cours_a = [
            new Cours("Cours1", "2", 1),
            new Cours("Cours2", "2.5", 3),
            new Cours("Cours3", "3", 5),
            new Cours("Cours4", "2", 3),
            new Cours("Cours5", "3", 3),
            new Cours("Cours6", "2", 4),
            new Cours("Cours7", "3", 5), // ** A SUPPRIMER **
            new Cours("Cours8", "4", 5),
            new Cours("Cours9", "3", 5) // ** A MODIFIER **
        ];
    }

    /**
     * Suppression de la base de données temporaire après les tests
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        if (self::$conn !== null) {
            self::$conn->exec('DROP DATABASE IF EXISTS ' . self::DB_NAME);
            print "SUPPRESSION DE LA BASE DE DONNÉES " . self::DB_NAME . " REUSSIE\n";
            self::$conn = null;
        }
    }

    /**
     * Retourne une connexion PDO à la base de données
     */
    protected function getConnection()
    {
        if (self::$conn === null) {
            self::$conn = new \PDO('mysql:host=localhost;dbname=' . self::DB_NAME . ';charset=utf8', 'root', '');
            self::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$conn;
    }

    /**
     * Test de sélection et affichage du premier professeur et cours
     */
    public function testPrintOne()
    {
        print __METHOD__ . "\n";
        $conn = $this->getConnection();

        // Prof
        $prof = Prof::printOne($conn);
        if ($prof !== null) {
            $prof_str = $prof->__toString();
            print "########## - PREMIER PROF EN BASE - ########## \n";
            print $prof_str . "\n";
            print "###############################################\n\n";

            $expected = self::$prof_a[0]->__toString();
            $this->assertEquals($expected, $prof_str, "Affichage du premier professeur");
        } else {
            print "Aucun professeur trouvé.\n";
        }

        // Cours
        $cours = Cours::printOne($conn);
        if ($cours !== null) {
            $cours_str = $cours->__toString();
            print "########## - PREMIER COURS EN BASE - ########## \n";
            print $cours_str . "\n";
            print "###############################################\n\n";

            $expected = self::$cours_a[0]->__toString();
            $this->assertEquals($expected, $cours_str, "Affichage du premier cours");
        } else {
            print "Aucun cours trouvé.\n";
        }
    }

    /**
     * Mise à jour d'un professeur et d'un cours
     */
    public function testUpdateOne()
    {
        print __METHOD__ . "\n";
        $conn = $this->getConnection();

        // Prof
        $prof = new Prof($this->nom, $this->prenom, $this->date, $this->lieu);
        $val = $prof->updateOne($conn, 10);
        $this->assertTrue($val, "Mise à jour du professeur");

        $updated_prof = Prof::printOne($conn, 10);
        if ($updated_prof !== null) {
            $this->assertEquals($prof->__toString(), $updated_prof->__toString(), "Validation de la mise à jour du professeur");
        }

        // Cours
        $cours = new Cours($this->intitule, $this->duree, 10);
        $val = $cours->updateOne($conn, 9);
        $this->assertTrue($val, "Mise à jour du cours");

        $updated_cours = Cours::printOne($conn, 9);
        if ($updated_cours !== null) {
            $this->assertEquals($cours->__toString(), $updated_cours->__toString(), "Validation de la mise à jour du cours");
        }
    }
}
