<?php
namespace spec\Wizad\SettingsBundle\Model;

use PhpSpec\ObjectBehavior;
use Wizad\SettingsBundle\Dal\ParametersStorageInterface;
use Wizad\SettingsBundle\DependencyInjection\ContainerInjectionManager;

class SettingsSpec extends ObjectBehavior
{
    function let(ParametersStorageInterface $parametersStorage, ContainerInjectionManager $containerInjectionManager)
    {
        $this->beConstructedWith($parametersStorage, $containerInjectionManager, [
            'hackday.catalog.product.bestSellsIds' => [
                'key' => 'hackday.catalog.product.bestSellsIds',
                'name' => 'Ids des meilleurs ventes séparés par des virgules',
                'default' => '001276988,001120716,000221024,001263450,001121110,001261630,000780287,000221262,000399900,000182165,001120654,001121401,000221166,000222445,001276990,001272423,000181850,000788355,001276910,001442456,000797321,000220673,001279640,000219606,001632749,000479182,000611657,001120964,001120728,001263452',
                'form' => [
                    'type' => 'text',
                ],
            ],
            'hackday.catalog.product.selectionIds' => [
                'key' => 'hackday.catalog.product.selectionIds',
                'name' => 'Ids pour la selection séparés par des virgules',
                'default' => '001156714,000188539,001632751,001880608,001883764,001078510,001616417,001887085,001518238,001618786,000481304,000010964,001878382,001844398,001853846,001354014,001613659,001172706,000750096,000472572,001120602,001880074,001521737,001882508,001879845,001223260,000156531,001837692,001882863,001443030,000188539',
                'form' => [
                    'type' => 'text',
                ],
            ],

        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Wizad\SettingsBundle\Model\Settings');
    }

    function it_is_a_settings_model()
    {
        $this->shouldImplement('Wizad\SettingsBundle\Model\SettingsInterface');
    }

    function it_should_find_valid_key()
    {
        $this->isValidKey('hackday.catalog.product.selectionIds')->shouldReturn(true);
        $this->isValidKey('hackday2.catalog.product.selectionIds')->shouldReturn(false);
    }
}