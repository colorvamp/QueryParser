# QueryParser
Class to convert Mongo Query Syntax into SQL syntax

Take Mongo Query Syntax and Convert into SQL Query Syntax
http://docs.mongodb.org/manual/reference/operator/query/

$sqlqry = QueryParser::parse($_REQUEST["mongoqry"]);
