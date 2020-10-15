<?php
namespace Networkteam\Import\Tests\DataProvider;

use Doctrine\DBAL\DriverManager;
use Networkteam\Import\DataProvider\DoctrineDbalDataProvider;
use PHPUnit\Framework\TestCase;

class DoctrineDbalDataProviderTest extends TestCase
{

    /**
     * @var DoctrineDbalDataProvider
     */
    public $dataProvider;

    /**
     * @var array
     */
    private $baseOptions = [
        DoctrineDbalDataProvider::KEY_PROVIDER_OPTIONS => [
            'path' => 'sqlite.db',
            'driver' => 'pdo_sqlite'
        ]
    ];

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    protected function setUp(): void
    {
        if (!class_exists('\PDO')) {
            $this->markTestSkipped('PDO is not installed');
        }
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite is not installed');
        }

        $this->createFixtureData();

        $this->dataProvider = new DoctrineDbalDataProvider();
        $this->dataProvider->setOptions($this->baseOptions);
    }

    protected function tearDown(): void
    {
        $this->dataProvider->close();
    }

    /**
     * @test
     */
    public function iteratesRows()
    {
        $this->dataProvider->setQuery('SELECT * FROM articles ORDER BY id ASC');

        $this->dataProvider->open();

        $rows = [];
        foreach ($this->dataProvider as $key => $row) {
            $rows[$key] = $row;
        }

        $this->assertEquals([
            [
                'id' => 13,
                'title' => 'A nice hat',
            ],
            [
                'id' => 42,
                'title' => 'Some fine shoes',
            ],
        ], $rows);
    }

    /**
     * @test
     */
    public function rewindExecutesQueryAgain()
    {
        $this->dataProvider->setQuery('SELECT * FROM articles ORDER BY id ASC');

        $this->dataProvider->open();

        $rows = iterator_to_array($this->dataProvider);
        $this->assertCount(2, $rows);

        $this->conn->executeUpdate('INSERT INTO articles VALUES (7, \'A whole lot of sevens\')');

        $rows = iterator_to_array($this->dataProvider);

        $this->assertEquals([
            [
                'id' => 7,
                'title' => 'A whole lot of sevens',
            ],
            [
                'id' => 13,
                'title' => 'A nice hat',
            ],
            [
                'id' => 42,
                'title' => 'Some fine shoes',
            ],
        ], $rows);
    }

    /**
     * @test
     */
    public function parameterIsUsedInExecute()
    {
        $this->dataProvider->setQuery('SELECT * FROM articles WHERE title LIKE :title ORDER BY id ASC');

        $options = $this->baseOptions;
        $options[DoctrineDbalDataProvider::KEY_PARAMETERS] = [
            'title' => '%hat%',
        ];
        $this->dataProvider->setOptions($options);

        $this->dataProvider->open();

        $rows = [];
        foreach ($this->dataProvider as $key => $row) {
            $rows[$key] = $row;
        }

        $this->assertEquals([
            [
                'id' => 13,
                'title' => 'A nice hat',
            ],
        ], $rows);
    }

    private function createFixtureData(): void
    {
        $this->conn = DriverManager::getConnection($this->baseOptions[DoctrineDbalDataProvider::KEY_PROVIDER_OPTIONS]);
        $this->conn->executeUpdate('DROP TABLE IF EXISTS articles');
        $this->conn->executeUpdate('CREATE TABLE articles (id INTEGER PRIMARY KEY, title TEXT)');
        $this->conn->executeUpdate('INSERT INTO articles VALUES (42, \'Some fine shoes\'), (13, \'A nice hat\')');
    }
}
