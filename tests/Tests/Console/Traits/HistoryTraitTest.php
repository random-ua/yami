<?php declare(strict_types=1);

namespace Tests\Console\Traits;

use PHPUnit\Framework\TestCase;
use Yami\Console\Traits\HistoryTrait;
use DateTime;

class HistoryTraitTest extends TestCase
{

    use HistoryTrait;

    const HISTORY_FILENAME = './tests/history.log';

    public function testLoadFilteredHistory(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(true, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3","ts":"1586008428","batchId":"3.1"}',
            '{"configId":"config-ignore","environmentName":"default","migration":"2020_04_04_141744_test_class_4","ts":"1586008528","batchId":"4.1"}',
            '{"configId":"config","environmentName":"default-ignore","migration":"2020_04_06_104613_test_class_5","ts":"1586008628","batchId":"5.1"}',
        ]);
        $this->assertEquals($this->history, [
            'config_default_2020_03_31_225419_test_class_1' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_03_31_225419_test_class_1',
                'ts' => '1586008228',
                'batchId' => '1.1'
            ],
            'config_default_2020_03_31_230630_test_class_2' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_03_31_230630_test_class_2',
                'ts' => '1586008328',
                'batchId' => '2.1'
            ],
            'config_default_2020_04_01_121712_test_class_3' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3',
                'ts' => '1586008428',
                'batchId' => '3.1'
            ],
        ]);
        $this->assertEquals($this->getLastBatchNo(), 3);
    }

    public function testAddToHistory(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(true, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3","ts":"1586008428","batchId":"3.1"}',
        ]);

        $timestamp = (new DateTime())->format('U');

        $this->addToHistory('2020_04_04_141744_test_class_4', 4, 1, $timestamp);

        $this->assertEquals($this->history, [
            'config_default_2020_03_31_225419_test_class_1' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_03_31_225419_test_class_1',
                'ts' => '1586008228',
                'batchId' => '1.1'
            ],
            'config_default_2020_03_31_230630_test_class_2' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_03_31_230630_test_class_2',
                'ts' => '1586008328',
                'batchId' => '2.1'
            ],
            'config_default_2020_04_01_121712_test_class_3' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3',
                'ts' => '1586008428',
                'batchId' => '3.1'
            ],
            'config_default_2020_04_04_141744_test_class_4' => [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_04_141744_test_class_4',
                'ts' => $timestamp,
                'batchId' => '4.1'
            ],
        ]);
        $this->assertEquals($this->getLastBatchNo(), 4);

        // Remove history log
        unlink(self::HISTORY_FILENAME);
    }

    public function testRemoveFromHistory(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(false, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3","ts":"1586008428","batchId":"3.1"}',
        ]);

        $this->removeFromHistory('2020_03_31_230630_test_class_2');

        $this->assertEquals($this->history, [
            'config_default_2020_03_31_225419_test_class_1' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_03_31_225419_test_class_1',
                'ts' => '1586008228',
                'batchId' => '1.1'
            ],
            'config_default_2020_04_01_121712_test_class_3' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3',
                'ts' => '1586008428',
                'batchId' => '3.1'
            ],
        ]);

        $this->assertEquals(explode("\n", trim(file_get_contents(self::HISTORY_FILENAME))), [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3","ts":"1586008428","batchId":"3.1"}',
        ]);

        // Remove history log
        unlink(self::HISTORY_FILENAME);
    }

    public function testMigrationHasRun(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(false, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3","ts":"1586008428","batchId":"3.1"}',
        ]);

        $this->assertEquals($this->migrationHasRun('2020_03_31_225419_test_class_1'), true);
        $this->assertEquals($this->migrationHasRun('2020_04_04_141744_test_class_4'), false);
    }

    public function testGetLastMigrationBatch(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(true, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3_1","ts":"1586008428","batchId":"3.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3_2","ts":"1586008428","batchId":"3.2"}',
            // the next one is deliberately wrong:
            '{"configId":"config","environmentName":"default","migration":"2020_04_04_141744_test_class_4","ts":"1586008528","batchId":"3.1"}',
            '{"configId":"config","environmentName":"default-ignore","migration":"2020_04_06_104613_test_class_5","ts":"1586008628","batchId":"5.1"}',
        ]);

        $this->assertEquals($this->getLastMigrationBatch(), [
            'config_default_2020_04_04_141744_test_class_4' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_04_141744_test_class_4',
                'ts' => '1586008528',
                'batchId' => '3.1' // deliberately wrong
            ]
        ]);

        // Now remove
        $this->removeFromHistory('2020_04_04_141744_test_class_4');

        $this->assertEquals($this->getLastMigrationBatch(), [
            'config_default_2020_04_01_121712_test_class_3_2' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3_2',
                'ts' => '1586008428',
                'batchId' => '3.2'
            ],
            'config_default_2020_04_01_121712_test_class_3_1' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3_1',
                'ts' => '1586008428',
                'batchId' => '3.1'
            ],
        ]);

        // Now remove
        $this->removeFromHistory('2020_04_01_121712_test_class_3_2');
        $this->removeFromHistory('2020_04_01_121712_test_class_3_1');

        $this->assertEquals($this->getLastMigrationBatch(), [
            'config_default_2020_03_31_230630_test_class_2' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_03_31_230630_test_class_2',
                'ts' => '1586008328',
                'batchId' => '2.1'
            ]
        ]);

        // Remove history log
        unlink(self::HISTORY_FILENAME);
    }

    public function testGetMigrationsToStep(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(true, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3_1","ts":"1586008428","batchId":"3.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3_2","ts":"1586008428","batchId":"3.2"}',
            // the next one is deliberately wrong:
            '{"configId":"config","environmentName":"default","migration":"2020_04_04_141744_test_class_4","ts":"1586008528","batchId":"3.1"}',
            '{"configId":"config","environmentName":"default-ignore","migration":"2020_04_06_104613_test_class_5","ts":"1586008628","batchId":"5.1"}',
        ]);

        $this->assertEquals($this->getMigrationsToStep(3), [
            'config_default_2020_04_04_141744_test_class_4' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_04_141744_test_class_4',
                'ts' => '1586008528',
                'batchId' => '3.1' // deliberately wrong
            ],
            'config_default_2020_04_01_121712_test_class_3_2' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3_2',
                'ts' => '1586008428',
                'batchId' => '3.2'
            ],
            'config_default_2020_04_01_121712_test_class_3_1' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3_1',
                'ts' => '1586008428',
                'batchId' => '3.1'
            ],
        ]);
    }

    public function testGetMigrationsToTarget(): void
    {
        $this->configId = 'config';
        $this->environment = (object) [
            'name' => 'default'
        ];
        $this->loadHistory(true, [
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_225419_test_class_1","ts":"1586008228","batchId":"1.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_03_31_230630_test_class_2","ts":"1586008328","batchId":"2.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3_1","ts":"1586008428","batchId":"3.1"}',
            '{"configId":"config","environmentName":"default","migration":"2020_04_01_121712_test_class_3_2","ts":"1586008428","batchId":"3.2"}',
            // the next one is deliberately wrong:
            '{"configId":"config","environmentName":"default","migration":"2020_04_04_141744_test_class_4","ts":"1586008528","batchId":"3.1"}',
            '{"configId":"config","environmentName":"default-ignore","migration":"2020_04_06_104613_test_class_5","ts":"1586008628","batchId":"5.1"}',
        ]);

        $this->assertEquals($this->getMigrationsToTarget('2020_04_01_121712_test_class_3_1'), [
            'config_default_2020_04_04_141744_test_class_4' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_04_141744_test_class_4',
                'ts' => '1586008528',
                'batchId' => '3.1' // deliberately wrong
            ],
            'config_default_2020_04_01_121712_test_class_3_2' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3_2',
                'ts' => '1586008428',
                'batchId' => '3.2'
            ],
            'config_default_2020_04_01_121712_test_class_3_1' => (object) [
                'configId' => 'config',
                'environmentName' => 'default',
                'migration' => '2020_04_01_121712_test_class_3_1',
                'ts' => '1586008428',
                'batchId' => '3.1'
            ],
        ]);
    }

}