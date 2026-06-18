<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function login($I, $email = null, $password = null)
    {
        $I->amOnPage('/');
        $I->click('Login');
        $I->wait(1);
        $I->fillField('E-Mail Address', $email ?? $_ENV['TEST_EMAIL']);
        $I->fillField('Password', $password ?? $_ENV['TEST_PW']);
        $I->click('.btn-primary');
        $I->wait(1);
    }
}
