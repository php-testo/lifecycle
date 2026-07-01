<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Unit\Fixture;

use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;

function fnPlainTest(): void {}

#[BeforeTest]
function fnSetUp(): void {}

#[AfterTest]
function fnTearDown(): void {}

#[BeforeClass]
function fnSetUpClass(): void {}

#[AfterClass]
function fnTearDownClass(): void {}

function fnAnotherPlainTest(): void {}
