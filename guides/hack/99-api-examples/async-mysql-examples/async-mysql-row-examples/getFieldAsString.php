<?hh

namespace Hack\UserDocumentation\API\Examples\AsyncMysql\Row\GFAsString;

require __DIR__ .'/../connect.inc.php';

use \Hack\UserDocumentation\API\Examples\AsyncMysql\ConnectionInfo as CI;

async function connect(\AsyncMysqlConnectionPool $pool):
  Awaitable<\AsyncMysqlConnection> {
  return await $pool->connect(
    CI::$host,
    CI::$port,
    CI::$db,
    CI::$user,
    CI::$passwd
  );
}
async function simple_query(): Awaitable<string> {
  $pool = new \AsyncMysqlConnectionPool(array());
  $conn = await connect($pool);
  $result = await $conn->query(
    'SELECT * FROM test_table WHERE userID < 50'
  );
  $conn->close();
  // A call to $result->rowBlocks() actually pops the first element of the
  // row block Vector. So the call actually mutates the Vector.
  $row_blocks = $result->rowBlocks();
  if (!$row_blocks->isEmpty()) {
    // An AsyncMysqlRowBlock
    $row_block = $row_blocks[0];
    if (!$row_block->isEmpty()) {
      // An AsyncMysqlRow
      $row = $row_block->getRow(0);
      return $row->getFieldAsString('name'); // string
    }
    return "";
  } else {
    return "";
  }
}

function run(): void {
  $r = \HH\Asio\join(simple_query());
  var_dump($r);
}

run();
