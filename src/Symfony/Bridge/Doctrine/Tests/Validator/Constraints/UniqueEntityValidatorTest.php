<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\Tests\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\Tests\DoctrineTestHelper;
use Symfony\Bridge\Doctrine\Tests\Fixtures\AssociatedEntityDto;
use Symfony\Bridge\Doctrine\Tests\Fixtures\AssociationEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\AssociationEntity2;
use Symfony\Bridge\Doctrine\Tests\Fixtures\CompositeIntIdEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\CompositeObjectNoToStringIdEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\CreateDoubleNameEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\DoubleNameEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\DoubleNullableNameEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\Dto;
use Symfony\Bridge\Doctrine\Tests\Fixtures\Employee;
use Symfony\Bridge\Doctrine\Tests\Fixtures\HireAnEmployee;
use Symfony\Bridge\Doctrine\Tests\Fixtures\Person;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdNoToStringEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdStringWrapperNameEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdWithPrivateNameEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleStringIdEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\Type\StringWrapper;
use Symfony\Bridge\Doctrine\Tests\Fixtures\Type\StringWrapperType;
use Symfony\Bridge\Doctrine\Tests\Fixtures\UpdateCompositeIntIdEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\UpdateCompositeObjectNoToStringIdEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\UpdateEmployeeProfile;
use Symfony\Bridge\Doctrine\Tests\Fixtures\UserUuidNameDto;
use Symfony\Bridge\Doctrine\Tests\Fixtures\UserUuidNameEntity;
use Symfony\Bridge\Doctrine\Tests\TestRepositoryFactory;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class UniqueEntityValidatorTest extends ConstraintValidatorTestCase
{
    private const EM_NAME = 'foo';

    protected ?ObjectManager $em;
    protected ManagerRegistry $registry;
    protected MockObject&EntityRepository $repository;
    protected TestRepositoryFactory $repositoryFactory;

    protected function setUp(): void
    {
        $this->repositoryFactory = new TestRepositoryFactory();

        $config = DoctrineTestHelper::createTestConfiguration();
        $config->setRepositoryFactory($this->repositoryFactory);

        if (!Type::hasType('string_wrapper')) {
            Type::addType('string_wrapper', StringWrapperType::class);
        }

        $this->em = DoctrineTestHelper::createTestEntityManager($config);
        $this->registry = $this->createRegistryMock($this->em);
        $this->createSchema($this->em);

        parent::setUp();
    }

    protected function createRegistryMock($em = null)
    {
        $registry = $this->createMock(ManagerRegistry::class);

        if (null === $em) {
            $registry->method('getManager')
                ->with($this->equalTo(self::EM_NAME))
                ->willThrowException(new \InvalidArgumentException());
        } else {
            $registry->method('getManager')
                ->with($this->equalTo(self::EM_NAME))
                ->willReturn($em);
        }

        return $registry;
    }

    protected function createValidator(): UniqueEntityValidator
    {
        return new UniqueEntityValidator($this->registry);
    }

    private function createSchema($em)
    {
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([
            $em->getClassMetadata(SingleIntIdEntity::class),
            $em->getClassMetadata(SingleIntIdWithPrivateNameEntity::class),
            $em->getClassMetadata(SingleIntIdNoToStringEntity::class),
            $em->getClassMetadata(DoubleNameEntity::class),
            $em->getClassMetadata(DoubleNullableNameEntity::class),
            $em->getClassMetadata(CompositeIntIdEntity::class),
            $em->getClassMetadata(AssociationEntity::class),
            $em->getClassMetadata(AssociationEntity2::class),
            $em->getClassMetadata(Person::class),
            $em->getClassMetadata(Employee::class),
            $em->getClassMetadata(CompositeObjectNoToStringIdEntity::class),
            $em->getClassMetadata(SingleIntIdStringWrapperNameEntity::class),
            $em->getClassMetadata(UserUuidNameEntity::class),
        ]);
    }

    /**
     * This is a functional test as there is a large integration necessary to get the validator working.
     */
    public function testValidateUniqueness()
    {
        $constraint = new UniqueEntity(message: 'myMessage', fields: ['name'], em: 'foo');

        $entity1 = new SingleIntIdEntity(1, 'Foo');
        $entity2 = new SingleIntIdEntity(2, 'Foo');

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($entity2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue($entity2)
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public static function provideUniquenessConstraints(): iterable
    {
        yield 'Doctrine style' => [new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name'],
            'em' => self::EM_NAME,
        ])];

        yield 'Named arguments' => [new UniqueEntity(message: 'myMessage', fields: ['name'], em: 'foo')];
    }

    public function testValidateEntityWithPrivatePropertyAndProxyObject()
    {
        $entity = new SingleIntIdWithPrivateNameEntity(1, 'Foo');
        $this->em->persist($entity);
        $this->em->flush();

        $this->em->clear();

        // this will load a proxy object
        $entity = $this->em->getReference(SingleIntIdWithPrivateNameEntity::class, 1);

        $this->validator->validate($entity, new UniqueEntity(
            fields: ['name'],
            em: self::EM_NAME,
        ));

        $this->assertNoViolation();
    }

    /**
     * @group legacy
     */
    public function testValidateEntityWithPrivatePropertyAndProxyObjectDoctrineStyle()
    {
        $entity = new SingleIntIdWithPrivateNameEntity(1, 'Foo');
        $this->em->persist($entity);
        $this->em->flush();

        $this->em->clear();

        // this will load a proxy object
        $entity = $this->em->getReference(SingleIntIdWithPrivateNameEntity::class, 1);

        $this->validator->validate($entity, new UniqueEntity([
            'fields' => ['name'],
            'em' => self::EM_NAME,
        ]));

        $this->assertNoViolation();
    }

    public function testValidateCustomErrorPath()
    {
        $entity1 = new SingleIntIdEntity(1, 'Foo');
        $entity2 = new SingleIntIdEntity(2, 'Foo');

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity2, new UniqueEntity(message: 'myMessage', fields: ['name'], em: 'foo', errorPath: 'bar'));

        $this->buildViolation('myMessage')
            ->atPath('property.path.bar')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue($entity2)
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    /**
     * @group legacy
     */
    public function testValidateCustomErrorPathDoctrineStyle()
    {
        $entity1 = new SingleIntIdEntity(1, 'Foo');
        $entity2 = new SingleIntIdEntity(2, 'Foo');

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity2, new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name'],
            'em' => 'foo',
            'errorPath' => 'bar',
        ]));

        $this->buildViolation('myMessage')
            ->atPath('property.path.bar')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue($entity2)
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public function testValidateUniquenessWithNull()
    {
        $entity1 = new SingleIntIdEntity(1, null);
        $entity2 = new SingleIntIdEntity(2, null);

        $this->em->persist($entity1);
        $this->em->persist($entity2);
        $this->em->flush();

        $this->validator->validate($entity1, new UniqueEntity(message: 'myMessage', fields: ['name'], em: 'foo'));

        $this->assertNoViolation();
    }

    /**
     * @dataProvider provideConstraintsWithIgnoreNullDisabled
     * @dataProvider provideConstraintsWithIgnoreNullEnabledOnFirstField
     */
    public function testValidateUniquenessWithIgnoreNullDisableOnSecondField(UniqueEntity $constraint)
    {
        $entity1 = new DoubleNameEntity(1, 'Foo', null);
        $entity2 = new DoubleNameEntity(2, 'Foo', null);

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($entity2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue('Foo')
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public static function provideConstraintsWithIgnoreNullDisabled(): iterable
    {
        yield 'Named arguments' => [new UniqueEntity(message: 'myMessage', fields: ['name', 'name2'], em: 'foo', ignoreNull: false)];
    }

    /**
     * @dataProvider provideConstraintsWithIgnoreNullEnabled
     */
    public function testAllConfiguredFieldsAreCheckedOfBeingMappedByDoctrineWithIgnoreNullEnabled(UniqueEntity $constraint)
    {
        $entity1 = new SingleIntIdEntity(1, null);

        $this->expectException(ConstraintDefinitionException::class);
        $this->validator->validate($entity1, $constraint);
    }

    /**
     * @dataProvider provideConstraintsWithIgnoreNullEnabled
     * @dataProvider provideConstraintsWithIgnoreNullEnabledOnFirstField
     */
    public function testNoValidationIfFirstFieldIsNullAndNullValuesAreIgnored(UniqueEntity $constraint)
    {
        $entity1 = new DoubleNullableNameEntity(1, null, 'Foo');
        $entity2 = new DoubleNullableNameEntity(2, null, 'Foo');

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($entity2, $constraint);

        $this->assertNoViolation();
    }

    public static function provideConstraintsWithIgnoreNullEnabled(): iterable
    {
        yield 'Named arguments' => [new UniqueEntity(message: 'myMessage', fields: ['name', 'name2'], em: 'foo', ignoreNull: true)];
    }

    public static function provideConstraintsWithIgnoreNullEnabledOnFirstField(): iterable
    {
        yield 'Named arguments (name field)' => [new UniqueEntity(message: 'myMessage', fields: ['name', 'name2'], em: 'foo', ignoreNull: 'name')];
    }

    public function testValidateUniquenessWithValidCustomErrorPath()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name', 'name2'],
            em: self::EM_NAME,
            errorPath: 'name2',
        );

        $entity1 = new DoubleNameEntity(1, 'Foo', 'Bar');
        $entity2 = new DoubleNameEntity(2, 'Foo', 'Bar');

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($entity2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name2')
            ->setParameter('{{ value }}', '"Bar"')
            ->setInvalidValue('Bar')
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public function testValidateUniquenessUsingCustomRepositoryMethod()
    {
        $this->em->getRepository(SingleIntIdEntity::class)->result = [];
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $entity1 = new SingleIntIdEntity(1, 'foo');

        $this->validator->validate($entity1, new UniqueEntity(message: 'myMessage', fields: ['name'], em: 'foo', repositoryMethod: 'findByCustom'));

        $this->assertNoViolation();
    }

    public function testValidateUniquenessWithUnrewoundArray()
    {
        $entity = new SingleIntIdEntity(1, 'foo');

        $returnValue = [
            $entity,
        ];
        next($returnValue);

        $this->em->getRepository(SingleIntIdEntity::class)->result = $returnValue;
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $this->validator->validate($entity, new UniqueEntity(message: 'myMessage', fields: ['name'], em: 'foo', repositoryMethod: 'findByCustom'));

        $this->assertNoViolation();
    }

    /**
     * @dataProvider resultTypesProvider
     */
    public function testValidateResultTypes($entity1, $result)
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            repositoryMethod: 'findByCustom',
        );

        $this->em->getRepository(SingleIntIdEntity::class)->result = $result;
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();
    }

    public static function resultTypesProvider(): array
    {
        $entity = new SingleIntIdEntity(1, 'foo');

        return [
            [$entity, [$entity]],
            [$entity, new \ArrayIterator([$entity])],
            [$entity, new ArrayCollection([$entity])],
        ];
    }

    public function testAssociatedEntity()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['single'],
            em: self::EM_NAME,
        );

        $entity1 = new SingleIntIdEntity(1, 'foo');
        $associated = new AssociationEntity();
        $associated->single = $entity1;
        $associated2 = new AssociationEntity();
        $associated2->single = $entity1;

        $this->em->persist($entity1);
        $this->em->persist($associated);
        $this->em->flush();

        $this->validator->validate($associated, $constraint);

        $this->assertNoViolation();

        $this->em->persist($associated2);
        $this->em->flush();

        $this->validator->validate($associated2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.single')
            ->setParameter('{{ value }}', 'foo')
            ->setInvalidValue($entity1)
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause([$associated, $associated2])
            ->assertRaised();
    }

    public function testValidateUniquenessNotToStringEntityWithAssociatedEntity()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['single'],
            em: self::EM_NAME,
        );

        $entity1 = new SingleIntIdNoToStringEntity(1, 'foo');
        $associated = new AssociationEntity2();
        $associated->single = $entity1;
        $associated2 = new AssociationEntity2();
        $associated2->single = $entity1;

        $this->em->persist($entity1);
        $this->em->persist($associated);
        $this->em->flush();

        $this->validator->validate($associated, $constraint);

        $this->assertNoViolation();

        $this->em->persist($associated2);
        $this->em->flush();

        $this->validator->validate($associated2, $constraint);

        $expectedValue = 'object("Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdNoToStringEntity") identified by (id => 1)';

        $this->buildViolation('myMessage')
            ->atPath('property.path.single')
            ->setParameter('{{ value }}', $expectedValue)
            ->setInvalidValue($entity1)
            ->setCause([$associated, $associated2])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public function testAssociatedEntityWithNull()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['single'],
            em: self::EM_NAME,
            ignoreNull: false,
        );

        $associated = new AssociationEntity();
        $associated->single = null;

        $this->em->persist($associated);
        $this->em->flush();

        $this->validator->validate($associated, $constraint);

        $this->assertNoViolation();
    }

    public function testAssociatedEntityReferencedByPrimaryKey()
    {
        $this->registry = $this->createRegistryMock($this->em);
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->em);
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $entity = new SingleIntIdEntity(1, 'foo');
        $associated = new AssociationEntity();
        $associated->single = $entity;

        $this->em->persist($entity);
        $this->em->persist($associated);
        $this->em->flush();

        $dto = new AssociatedEntityDto();
        $dto->singleId = 1;

        $this->validator->validate($dto, new UniqueEntity(
            fields: ['singleId' => 'single'],
            entityClass: AssociationEntity::class,
        ));

        $this->buildViolation('This value is already used.')
            ->atPath('property.path.single')
            ->setParameter('{{ value }}', 1)
            ->setInvalidValue(1)
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause([$associated])
            ->assertRaised();
    }

    public function testValidateUniquenessWithArrayValue()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['phoneNumbers'],
            em: self::EM_NAME,
            repositoryMethod: 'findByCustom',
        );

        $entity1 = new SingleIntIdEntity(1, 'foo');
        $entity1->phoneNumbers[] = 123;

        $this->em->getRepository(SingleIntIdEntity::class)->result = $entity1;

        $this->em->persist($entity1);
        $this->em->flush();

        $entity2 = new SingleIntIdEntity(2, 'bar');
        $entity2->phoneNumbers[] = 123;
        $this->em->persist($entity2);
        $this->em->flush();

        $this->validator->validate($entity2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.phoneNumbers')
            ->setParameter('{{ value }}', 'array')
            ->setInvalidValue([123])
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public function testDedicatedEntityManagerNullObject()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
        );

        $this->em = null;
        $this->registry = $this->createRegistryMock($this->em);
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $entity = new SingleIntIdEntity(1, null);

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Object manager "foo" does not exist.');

        $this->validator->validate($entity, $constraint);
    }

    public function testEntityManagerNullObject()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            // no "em" option set
        );

        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $entity = new SingleIntIdEntity(1, null);

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Unable to find the object manager associated with an entity of class "Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity"');

        $this->validator->validate($entity, $constraint);
    }

    public function testValidateUniquenessOnNullResult()
    {
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
        );

        $entity = new SingleIntIdEntity(1, null);

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($entity, $constraint);
        $this->assertNoViolation();
    }

    public function testValidateInheritanceUniqueness()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            entityClass: Person::class,
        );

        $entity1 = new Person(1, 'Foo');
        $entity2 = new Employee(2, 'Foo');

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($entity2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setInvalidValue('Foo')
            ->setCode('23bd9dbf-6b9b-41cd-a99e-4844bcf3077f')
            ->setCause([$entity1])
            ->setParameters(['{{ value }}' => '"Foo"'])
            ->assertRaised();
    }

    public function testInvalidateRepositoryForInheritance()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            entityClass: SingleStringIdEntity::class,
        );

        $entity = new Person(1, 'Foo');

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The "Symfony\Bridge\Doctrine\Tests\Fixtures\SingleStringIdEntity" entity repository does not support the "Symfony\Bridge\Doctrine\Tests\Fixtures\Person" entity. The entity should be an instance of or extend "Symfony\Bridge\Doctrine\Tests\Fixtures\SingleStringIdEntity".');

        $this->validator->validate($entity, $constraint);
    }

    public function testValidateUniquenessWithCompositeObjectNoToStringIdEntity()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['objectOne', 'objectTwo'],
            em: self::EM_NAME,
        );

        $objectOne = new SingleIntIdNoToStringEntity(1, 'foo');
        $objectTwo = new SingleIntIdNoToStringEntity(2, 'bar');

        $this->em->persist($objectOne);
        $this->em->persist($objectTwo);
        $this->em->flush();

        $entity = new CompositeObjectNoToStringIdEntity($objectOne, $objectTwo);

        $this->em->persist($entity);
        $this->em->flush();

        $newEntity = new CompositeObjectNoToStringIdEntity($objectOne, $objectTwo);

        $this->validator->validate($newEntity, $constraint);

        $expectedValue = 'object("Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdNoToStringEntity") identified by (id => 1)';

        $this->buildViolation('myMessage')
            ->atPath('property.path.objectOne')
            ->setParameter('{{ value }}', $expectedValue)
            ->setInvalidValue($objectOne)
            ->setCause([$entity])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public function testValidateUniquenessWithCustomDoctrineTypeValue()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
        );

        $existingEntity = new SingleIntIdStringWrapperNameEntity(1, new StringWrapper('foo'));

        $this->em->persist($existingEntity);
        $this->em->flush();

        $newEntity = new SingleIntIdStringWrapperNameEntity(2, new StringWrapper('foo'));

        $this->validator->validate($newEntity, $constraint);

        $expectedValue = 'object("Symfony\Bridge\Doctrine\Tests\Fixtures\Type\StringWrapper")';

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setParameter('{{ value }}', $expectedValue)
            ->setInvalidValue($existingEntity->name)
            ->setCause([$existingEntity])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    /**
     * This is a functional test as there is a large integration necessary to get the validator working.
     */
    public function testValidateUniquenessCause()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
        );

        $entity1 = new SingleIntIdEntity(1, 'Foo');
        $entity2 = new SingleIntIdEntity(2, 'Foo');

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity1);
        $this->em->flush();

        $this->validator->validate($entity1, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($entity2, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue($entity2)
            ->setCause([$entity1])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    /**
     * @dataProvider resultWithEmptyIterator
     */
    public function testValidateUniquenessWithEmptyIterator($entity, $result)
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            repositoryMethod: 'findByCustom',
        );

        $this->em->getRepository(SingleIntIdEntity::class)->result = $result;
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();
    }

    public function testValueMustBeObject()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
        );

        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate('foo', $constraint);
    }

    public function testValueCanBeNull()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
        );

        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public static function resultWithEmptyIterator(): array
    {
        $entity = new SingleIntIdEntity(1, 'foo');

        return [
            [$entity, new class implements \Iterator {
                public function current(): mixed
                {
                    return null;
                }

                public function valid(): bool
                {
                    return false;
                }

                public function next(): void
                {
                }

                public function key(): mixed
                {
                    return false;
                }

                public function rewind(): void
                {
                }
            }],
            [$entity, new class implements \Iterator {
                public function current(): mixed
                {
                    return false;
                }

                public function valid(): bool
                {
                    return false;
                }

                public function next(): void
                {
                }

                public function key(): mixed
                {
                    return false;
                }

                public function rewind(): void
                {
                }
            }],
        ];
    }

    public function testValidateDTOUniqueness()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            entityClass: Person::class,
        );

        $entity = new Person(1, 'Foo');
        $dto = new HireAnEmployee('Foo');

        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setInvalidValue('Foo')
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause([$entity])
            ->setParameters(['{{ value }}' => '"Foo"'])
            ->assertRaised();
    }

    /**
     * @group legacy
     */
    public function testValidateDTOUniquenessDoctrineStyle()
    {
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name'],
            'em' => self::EM_NAME,
            'entityClass' => Person::class,
        ]);

        $entity = new Person(1, 'Foo');
        $dto = new HireAnEmployee('Foo');

        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($entity, $constraint);

        $this->assertNoViolation();

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setInvalidValue('Foo')
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause([$entity])
            ->setParameters(['{{ value }}' => '"Foo"'])
            ->assertRaised();
    }

    public function testValidateMappingOfFieldNames()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['primaryName' => 'name', 'secondaryName' => 'name2'],
            em: self::EM_NAME,
            entityClass: DoubleNameEntity::class,
        );

        $entity = new DoubleNameEntity(1, 'Foo', 'Bar');
        $dto = new CreateDoubleNameEntity('Foo', 'Bar');

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue('Foo')
            ->setCause([$entity])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    /**
     * @group legacy
     */
    public function testValidateMappingOfFieldNamesDoctrineStyle()
    {
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['primaryName' => 'name', 'secondaryName' => 'name2'],
            'em' => self::EM_NAME,
            'entityClass' => DoubleNameEntity::class,
        ]);

        $entity = new DoubleNameEntity(1, 'Foo', 'Bar');
        $dto = new CreateDoubleNameEntity('Foo', 'Bar');

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setParameter('{{ value }}', '"Foo"')
            ->setInvalidValue('Foo')
            ->setCause([$entity])
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->assertRaised();
    }

    public function testInvalidateDTOFieldName()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The field "primaryName" is not a property of class "Symfony\Bridge\Doctrine\Tests\Fixtures\HireAnEmployee".');
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['primaryName' => 'name'],
            em: self::EM_NAME,
            entityClass: SingleStringIdEntity::class,
        );

        $dto = new HireAnEmployee('Foo');
        $this->validator->validate($dto, $constraint);
    }

    /**
     * @group legacy
     */
    public function testInvalidateDTOFieldNameDoctrineStyle()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The field "primaryName" is not a property of class "Symfony\Bridge\Doctrine\Tests\Fixtures\HireAnEmployee".');
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['primaryName' => 'name'],
            'em' => self::EM_NAME,
            'entityClass' => SingleStringIdEntity::class,
        ]);

        $dto = new HireAnEmployee('Foo');
        $this->validator->validate($dto, $constraint);
    }

    public function testInvalidateEntityFieldName()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The field "name2" is not mapped by Doctrine, so it cannot be validated for uniqueness.');
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name2'],
            em: self::EM_NAME,
            entityClass: SingleStringIdEntity::class,
        );

        $dto = new HireAnEmployee('Foo');
        $this->validator->validate($dto, $constraint);
    }

    /**
     * @group legacy
     */
    public function testInvalidateEntityFieldNameDoctrineStyle()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The field "name2" is not mapped by Doctrine, so it cannot be validated for uniqueness.');
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name2'],
            'em' => self::EM_NAME,
            'entityClass' => SingleStringIdEntity::class,
        ]);

        $dto = new HireAnEmployee('Foo');
        $this->validator->validate($dto, $constraint);
    }

    public function testValidateDTOUniquenessWhenUpdatingEntity()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            entityClass: Person::class,
            identifierFieldNames: ['id'],
        );

        $entity1 = new Person(1, 'Foo');
        $entity2 = new Person(2, 'Bar');

        $this->em->persist($entity1);
        $this->em->persist($entity2);
        $this->em->flush();

        $dto = new UpdateEmployeeProfile(2, 'Foo');

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setInvalidValue('Foo')
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause([$entity1])
            ->setParameters(['{{ value }}' => '"Foo"'])
            ->assertRaised();
    }

    /**
     * @group legacy
     */
    public function testValidateDTOUniquenessWhenUpdatingEntityDoctrineStyle()
    {
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name'],
            'em' => self::EM_NAME,
            'entityClass' => Person::class,
            'identifierFieldNames' => ['id'],
        ]);

        $entity1 = new Person(1, 'Foo');
        $entity2 = new Person(2, 'Bar');

        $this->em->persist($entity1);
        $this->em->persist($entity2);
        $this->em->flush();

        $dto = new UpdateEmployeeProfile(2, 'Foo');

        $this->validator->validate($dto, $constraint);

        $this->buildViolation('myMessage')
            ->atPath('property.path.name')
            ->setInvalidValue('Foo')
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->setCause([$entity1])
            ->setParameters(['{{ value }}' => '"Foo"'])
            ->assertRaised();
    }

    public function testValidateDTOUniquenessWhenUpdatingEntityWithTheSameValue()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            em: self::EM_NAME,
            entityClass: CompositeIntIdEntity::class,
            identifierFieldNames: ['id1', 'id2'],
        );

        $entity = new CompositeIntIdEntity(1, 2, 'Foo');

        $this->em->persist($entity);
        $this->em->flush();

        $dto = new UpdateCompositeIntIdEntity(1, 2, 'Foo');

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @group legacy
     */
    public function testValidateDTOUniquenessWhenUpdatingEntityWithTheSameValueDoctrineStyle()
    {
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name'],
            'em' => self::EM_NAME,
            'entityClass' => CompositeIntIdEntity::class,
            'identifierFieldNames' => ['id1', 'id2'],
        ]);

        $entity = new CompositeIntIdEntity(1, 2, 'Foo');

        $this->em->persist($entity);
        $this->em->flush();

        $dto = new UpdateCompositeIntIdEntity(1, 2, 'Foo');

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateIdentifierMappingOfFieldNames()
    {
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['object1' => 'objectOne', 'object2' => 'objectTwo'],
            em: self::EM_NAME,
            entityClass: CompositeObjectNoToStringIdEntity::class,
            identifierFieldNames: ['object1' => 'objectOne', 'object2' => 'objectTwo'],
        );

        $objectOne = new SingleIntIdNoToStringEntity(1, 'foo');
        $objectTwo = new SingleIntIdNoToStringEntity(2, 'bar');

        $this->em->persist($objectOne);
        $this->em->persist($objectTwo);
        $this->em->flush();

        $entity = new CompositeObjectNoToStringIdEntity($objectOne, $objectTwo);

        $this->em->persist($entity);
        $this->em->flush();

        $dto = new UpdateCompositeObjectNoToStringIdEntity($objectOne, $objectTwo, 'Foo');

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @group legacy
     */
    public function testValidateIdentifierMappingOfFieldNamesDoctrineStyle()
    {
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['object1' => 'objectOne', 'object2' => 'objectTwo'],
            'em' => self::EM_NAME,
            'entityClass' => CompositeObjectNoToStringIdEntity::class,
            'identifierFieldNames' => ['object1' => 'objectOne', 'object2' => 'objectTwo'],
        ]);

        $objectOne = new SingleIntIdNoToStringEntity(1, 'foo');
        $objectTwo = new SingleIntIdNoToStringEntity(2, 'bar');

        $this->em->persist($objectOne);
        $this->em->persist($objectTwo);
        $this->em->flush();

        $entity = new CompositeObjectNoToStringIdEntity($objectOne, $objectTwo);

        $this->em->persist($entity);
        $this->em->flush();

        $dto = new UpdateCompositeObjectNoToStringIdEntity($objectOne, $objectTwo, 'Foo');

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }

    public function testInvalidateMissingIdentifierFieldName()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The "Symfony\Bridge\Doctrine\Tests\Fixtures\CompositeObjectNoToStringIdEntity" entity identifier field names should be "objectOne, objectTwo", not "objectTwo".');
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['object1' => 'objectOne', 'object2' => 'objectTwo'],
            em: self::EM_NAME,
            entityClass: CompositeObjectNoToStringIdEntity::class,
            identifierFieldNames: ['object2' => 'objectTwo'],
        );

        $objectOne = new SingleIntIdNoToStringEntity(1, 'foo');
        $objectTwo = new SingleIntIdNoToStringEntity(2, 'bar');

        $this->em->persist($objectOne);
        $this->em->persist($objectTwo);
        $this->em->flush();

        $entity = new CompositeObjectNoToStringIdEntity($objectOne, $objectTwo);

        $this->em->persist($entity);
        $this->em->flush();

        $dto = new UpdateCompositeObjectNoToStringIdEntity($objectOne, $objectTwo, 'Foo');
        $this->validator->validate($dto, $constraint);
    }

    /**
     * @group legacy
     */
    public function testInvalidateMissingIdentifierFieldNameDoctrineStyle()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('The "Symfony\Bridge\Doctrine\Tests\Fixtures\CompositeObjectNoToStringIdEntity" entity identifier field names should be "objectOne, objectTwo", not "objectTwo".');
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['object1' => 'objectOne', 'object2' => 'objectTwo'],
            'em' => self::EM_NAME,
            'entityClass' => CompositeObjectNoToStringIdEntity::class,
            'identifierFieldNames' => ['object2' => 'objectTwo'],
        ]);

        $objectOne = new SingleIntIdNoToStringEntity(1, 'foo');
        $objectTwo = new SingleIntIdNoToStringEntity(2, 'bar');

        $this->em->persist($objectOne);
        $this->em->persist($objectTwo);
        $this->em->flush();

        $entity = new CompositeObjectNoToStringIdEntity($objectOne, $objectTwo);

        $this->em->persist($entity);
        $this->em->flush();

        $dto = new UpdateCompositeObjectNoToStringIdEntity($objectOne, $objectTwo, 'Foo');
        $this->validator->validate($dto, $constraint);
    }

    public function testUninitializedValueThrowException()
    {
        $this->expectExceptionMessage('Typed property Symfony\Bridge\Doctrine\Tests\Fixtures\Dto::$foo must not be accessed before initialization');
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['foo' => 'name'],
            em: self::EM_NAME,
            entityClass: DoubleNameEntity::class,
        );

        $entity = new DoubleNameEntity(1, 'Foo', 'Bar');
        $dto = new Dto();

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($dto, $constraint);
    }

    /**
     * @group legacy
     */
    public function testUninitializedValueThrowExceptionDoctrineStyle()
    {
        $this->expectExceptionMessage('Typed property Symfony\Bridge\Doctrine\Tests\Fixtures\Dto::$foo must not be accessed before initialization');
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['foo' => 'name'],
            'em' => self::EM_NAME,
            'entityClass' => DoubleNameEntity::class,
        ]);

        $entity = new DoubleNameEntity(1, 'Foo', 'Bar');
        $dto = new Dto();

        $this->em->persist($entity);
        $this->em->flush();

        $this->validator->validate($dto, $constraint);
    }

    public function testEntityManagerNullObjectWhenDTO()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Unable to find the object manager associated with an entity of class "Symfony\Bridge\Doctrine\Tests\Fixtures\Person"');
        $constraint = new UniqueEntity(
            message: 'myMessage',
            fields: ['name'],
            entityClass: Person::class,
            // no "em" option set
        );

        $this->em = null;
        $this->registry = $this->createRegistryMock($this->em);
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $dto = new HireAnEmployee('Foo');

        $this->validator->validate($dto, $constraint);
    }

    /**
     * @group legacy
     */
    public function testEntityManagerNullObjectWhenDTODoctrineStyle()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Unable to find the object manager associated with an entity of class "Symfony\Bridge\Doctrine\Tests\Fixtures\Person"');
        $constraint = new UniqueEntity([
            'message' => 'myMessage',
            'fields' => ['name'],
            'entityClass' => Person::class,
            // no "em" option set
        ]);

        $this->em = null;
        $this->registry = $this->createRegistryMock($this->em);
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $dto = new HireAnEmployee('Foo');

        $this->validator->validate($dto, $constraint);
    }

    public function testUuidIdentifierWithSameValueDifferentInstanceDoesNotCauseViolation()
    {
        $uuidString = 'ec562e21-1fc8-4e55-8de7-a42389ac75c5';
        $existingPerson = new UserUuidNameEntity(Uuid::fromString($uuidString), 'Foo Bar');
        $this->em->persist($existingPerson);
        $this->em->flush();

        $dto = new UserUuidNameDto(Uuid::fromString($uuidString), 'Foo Bar', '');

        $constraint = new UniqueEntity(
            fields: ['fullName'],
            entityClass: UserUuidNameEntity::class,
            identifierFieldNames: ['id'],
            em: self::EM_NAME,
        );

        $this->validator->validate($dto, $constraint);

        $this->assertNoViolation();
    }
}
