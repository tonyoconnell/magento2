<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Newsletter\Controller;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Controller\Manage
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAccountServiceMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->formKeyValidatorMock = $this->getMockBuilder('Magento\Core\App\Action\FormKeyValidator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAccountServiceMock =
            $this->getMockBuilder('Magento\Customer\Service\V1\CustomerAccountServiceInterface')
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->controller = $objectManager->getObject('Magento\Newsletter\Controller\Manage', [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'messageManager' => $this->messageManagerMock,
                'redirect' => $this->redirectMock,
                'customerSession' => $this->customerSessionMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'customerAccountService' => $this->customerAccountServiceMock
            ]);
    }

    public function testSaveActionInvalidFormKey()
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(false));
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, 'customer/account/', []);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->never())
            ->method('addError');
        $this->controller->saveAction();
    }

    public function testSaveActionNoCustomerInSession()
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(null));
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, 'customer/account/', []);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Something went wrong while saving your subscription.');
        $this->controller->saveAction();
    }

    public function testSaveActionWithException()
    {
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue(1));
        $this->customerAccountServiceMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->throwException(new \Magento\Exception\NoSuchEntityException('customerId', 'value')));
        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, 'customer/account/', []);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');
        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Something went wrong while saving your subscription.');
        $this->controller->saveAction();
    }
}
