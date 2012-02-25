<?PHP
namespace DBALExt\Queries;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

abstract class ClausesQuery extends Query {
  private $clauses;
  
  
  public function __construct(Connection $conn, Schema $schema) {
    parent::__construct($conn, $schema);
    
    $this->clauses = $this->getClauses();
  }
  
  
  abstract protected function getClauses();
  
  
  protected function getClause($name) {
    return $this->clauses[$name];
  }
  
  
  protected function setClause($name, Clauses\Clause $clause) {
    if (!array_key_exists($name, $this->clauses)) {
      throw new \InvalidArgumentException(sprintf("Unknown clause specified: '%s'", $name));
    }
    
    $this->clauses[$name] = $clause;
  }
  
  
  public function toSQL() {
    $output = array();
    foreach ($this->clauses as $clause_name => $clause) {
      if ($clause === NULL || $clause->isEmpty()) {
        continue;
      }
      
      $output[] = $clause->toSQL();
    }
    
    return implode("\n", $output);
  }
  
  
  public function __toString() {
    return $this->toSQL();
  }
  
  
  public function getParamValues() {
    $values = array();
    foreach ($this->clauses as $clause) {
      if ($clause instanceof Clauses\WhereClause) {
        $values = array_merge($values,  $this->getClause('WHERE')->getParamValues());
      }
    }
    return $values;
  }
  
  
  public function getParamTypes() {
    $types = array();
    foreach ($this->clauses as $clause) {
      if ($clause instanceof Clauses\WhereClause) {
        $types = array_merge($types, $this->getClause('WHERE')->getParamTypes());
      }
    }
    return $types;
  }
}