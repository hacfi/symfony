<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Bridge\Twig\Extension\HttpKernelRuntime;
use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentUriGenerator;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class HttpKernelExtensionTest extends TestCase
{
    public function testFragmentWithError()
    {
        $renderer = $this->getFragmentHandler(new \Exception('foo'));

        $this->expectException(\Twig\Error\RuntimeError::class);

        $this->renderTemplate($renderer);
    }

    public function testRenderFragment()
    {
        $renderer = $this->getFragmentHandler(new Response('html'));

        $response = $this->renderTemplate($renderer);

        $this->assertEquals('html', $response);
    }

    public function testUnknownFragmentRenderer()
    {
        $renderer = new FragmentHandler(new RequestStack());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "inline" renderer does not exist.');

        $renderer->render('/foo');
    }

    public function testGenerateFragmentUri()
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));

        $fragmentHandler = new FragmentHandler($requestStack);
        $fragmentUriGenerator = new FragmentUriGenerator('/_fragment', new UriSigner('s3cr3t'), $requestStack);

        $kernelRuntime = new HttpKernelRuntime($fragmentHandler, $fragmentUriGenerator);

        $loader = new ArrayLoader([
            'index' => \sprintf(<<<TWIG
{{ fragment_uri(controller("%s::templateAction", {template: "foo.html.twig"})) }}
TWIG
                , str_replace('\\', '\\\\', TemplateController::class)), ]);
        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new HttpKernelExtension());

        $loader = $this->createMock(RuntimeLoaderInterface::class);
        $loader->expects($this->any())->method('load')->willReturnMap([
            [HttpKernelRuntime::class, $kernelRuntime],
        ]);
        $twig->addRuntimeLoader($loader);

        $this->assertMatchesRegularExpression('#/_fragment\?_hash=.+&amp;_path=template%3Dfoo.html.twig%26_format%3Dhtml%26_locale%3Den%26_controller%3DSymfony%255CBundle%255CFrameworkBundle%255CController%255CTemplateController%253A%253AtemplateAction$#', $twig->render('index'));
    }

    protected function getFragmentHandler($returnOrException): FragmentHandler
    {
        $strategy = $this->createMock(FragmentRendererInterface::class);
        $strategy->expects($this->once())->method('getName')->willReturn('inline');

        $mocker = $strategy->expects($this->once())->method('render');
        if ($returnOrException instanceof \Exception) {
            $mocker->willThrowException($returnOrException);
        } else {
            $mocker->willReturn($returnOrException);
        }

        $context = new RequestStack();

        $context->push(Request::create('/'));

        return new FragmentHandler($context, [$strategy], false);
    }

    protected function renderTemplate(FragmentHandler $renderer, $template = '{{ render("foo") }}')
    {
        $loader = new ArrayLoader(['index' => $template]);
        $twig = new Environment($loader, ['debug' => true, 'cache' => false]);
        $twig->addExtension(new HttpKernelExtension());

        $loader = $this->createMock(RuntimeLoaderInterface::class);
        $loader->expects($this->any())->method('load')->willReturnMap([
            ['Symfony\Bridge\Twig\Extension\HttpKernelRuntime', new HttpKernelRuntime($renderer)],
        ]);
        $twig->addRuntimeLoader($loader);

        return $twig->render('index');
    }
}
