<?php

declare(strict_types=1);

namespace Faker\Provider {
    function join(mixed $f, mixed $s): string
    {
        if (is_string($s)) {
            return implode($s, $f);
        }

        return implode($f, $s);
    }
}

namespace Library\Feature\Maintenance\Migrate\Console {
    use Faker\Factory;
    use Faker\Generator;
    use Library\Feature\User;
    use Library\Infrastructure\Database\Connection;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    final class MigrationSeedCommand extends Command
    {
        private Generator $faker;

        public function __construct(private Connection $connection)
        {
            $this->faker = Factory::create();

            parent::__construct();
        }

        protected function configure(): void
        {
            $this
                ->setName('migrate:seed')
                ->setDescription('Insert test data to DB');
        }

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $output->write('Inserting Users…');

            $this->connection->insert(
                'users',
                ['guid', 'login', 'password', 'token', 'role', 'first_name', 'last_name'],
                $this->getUsers(),
            );

            $output->writeln(' <fg=green>OK!</>');

            $output->write('Inserting Authors…');

            $authors = $this->getAuthors();

            $this->connection->insert(
                'authors',
                ['guid', 'name'],
                array_values($authors),
            );

            $output->writeln(' <fg=green>OK!</>');

            $output->write('Inserting Books…');

            $books = $this->getBooks();

            $this->connection->insert(
                'books',
                ['guid', 'isbn', 'title', 'description'],
                array_values($books),
            );

            $output->writeln(' <fg=green>OK!</>');

            $output->write('Attaching Authors to Books…');

            $authorsBooks = [];

            foreach ($books as $book) {
                foreach (array_rand($authors, random_int(2, 5)) as $authorGuid) {
                    $authorsBooks[] = [
                        'author' => $authorGuid,
                        'book' => $book['guid'],
                    ];
                }
            }

            $this->connection->insert('authors_books', ['author', 'book'], $authorsBooks);

            $output->writeln(' <fg=green>OK!</>');

            return Command::SUCCESS;
        }

        private function getUsers(): array
        {
            $users = [];

            $users[] = [
                'guid' => $this->faker->uuid,
                'login' => 'reader',
                'password' => password_hash($this->faker->password(), PASSWORD_DEFAULT),
                'token' => 'reader',
                'role' => User::READER,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
            ];

            $users[] = [
                'guid' => $this->faker->uuid,
                'login' => 'librarian',
                'password' => password_hash($this->faker->password(), PASSWORD_DEFAULT),
                'token' => 'librarian',
                'role' => User::LIBRARIAN,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
            ];

            return $users;
        }

        private function getAuthors(): array
        {
            $authors = [];

            foreach (range(0, 4) as $n) {
                $guid = $this->faker->uuid;

                $authors[$guid] = [
                    'guid' => $guid,
                    'name' => $this->faker->name,
                ];
            }

            return $authors;
        }

        private function getBooks(): array
        {
            $books = [];

            foreach (range(0, 9) as $n) {
                $guid = $this->faker->uuid;

                $books[$guid] = [
                    'guid' => $guid,
                    'isbn' => $this->faker->isbn13,
                    'title' => $this->faker->sentence(3),
                    'description' => $this->faker->realText(120),
                ];
            }

            return $books;
        }
    }
}
