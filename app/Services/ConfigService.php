<?php


namespace App\Services;


interface ConfigService
{
    // 获取所有配置
    public function getAll(): array;

    // 根据key获取value
    public function getOne(string $key): string;

}
