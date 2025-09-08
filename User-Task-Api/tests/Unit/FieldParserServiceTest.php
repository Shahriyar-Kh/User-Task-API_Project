<?php
// tests/Unit/FieldParserServiceTest.php
namespace Tests\Unit;


use App\Services\FieldParserService;
use PHPUnit\Framework\TestCase;


class FieldParserServiceTest extends TestCase
{
public function test_parses_structured_text()
{
$text = <<<TXT
Name: John Doe
Email: john@example.com
Phone: +1 (555) 123-4567
Address: 123 Main St
City: New York
State: NY
Zip: 10001
DOB: 01-09-1990
Gender: Male
Occupation: Engineer
TXT;


$svc = new FieldParserService();
$out = $svc->parse($text);


$this->assertSame('John Doe', $out['name']);
$this->assertSame('john@example.com', $out['email']);
$this->assertSame('+1 (555) 123-4567', $out['phone']);
$this->assertSame('1990-09-01', $out['dob']);
}
}