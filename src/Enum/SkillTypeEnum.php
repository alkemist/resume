<?php
namespace App\Enum;

enum SkillTypeEnum: string implements \JsonSerializable
{
    case Software = 'software';
    case Framework = 'framework';
    case Platform = 'platform';
    case Language = 'language';
    case OS = 'os';
    case Version = 'version';

    public function toString(): string
    {
        return match($this) {
            self::Software => 'Software',
            self::Framework => 'Framework',
            self::Platform => 'Platform',
            self::Language => 'Language',
            self::OS => 'OS',
            self::Version => 'Version',
        };
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}