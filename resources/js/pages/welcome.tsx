import { Head, usePage, router } from '@inertiajs/react';
import { useForm, type UseFormSetError } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import React, { useEffect, useRef } from 'react';

import { store as leadStore } from '@/routes/lead';
import { formatPhoneInput, normalizePhone } from '@/lib/phone';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';

import {
    User,
    Mail,
    Phone,
    DollarSign,
    FileText,
    Loader2,
    ArrowRight,
    AlertCircle,
    CheckCircle2,
} from 'lucide-react';

const schema = z.object({
    name: z.string().min(2, 'Минимум 2 символа'),
    email: z.string().min(1, 'Введите email').email('Некорректный email'),
    phone: z
        .string()
        .min(1, 'Введите телефон')
        .transform(normalizePhone)
        .refine((value) => /^\+7\d{10}$/.test(value), 'Введите телефон'),
    price: z
        .string()
        .min(1, 'Введите цену')
        .refine((val) => !Number.isNaN(Number(val)) && Number(val) > 0, {
            message: 'Цена должна быть положительным числом',
        }),
});

type FormValues = z.infer<typeof schema>;

interface PageProps {
    errors?: Record<string, string | string[]>;
    flash?: { error?: string | null; success?: string | null };
}

export default function Welcome() {
    const { errors: pageErrors, flash } = usePage<PageProps>().props;
    const startedAt = useRef<number>(Date.now());

    const {
        register,
        handleSubmit,
        setError,
        reset,
        formState: { errors, isSubmitting },
    } = useForm<FormValues>({
        resolver: zodResolver(schema),
        mode: 'onTouched',
        reValidateMode: 'onChange',
        defaultValues: {
            name: '',
            email: '',
            phone: '',
            price: '',
        },
    });

    useEffect(() => {
        if (!pageErrors) return;

        applyServerErrors(pageErrors, setError);
    }, [pageErrors, setError]);

    const onSubmit = (data: FormValues) => {
        const spentMoreThan30s = Date.now() - startedAt.current >= 30_000;

        router.post(
            leadStore.url(),
            { ...data, spent_more_than_30_seconds: spentMoreThan30s },
            {
                preserveScroll: true,
                onSuccess: (page: { props: PageProps }) => {
                    if (page.props.flash?.success) {
                        reset();
                    }
                },
            },
        );
    };

    return (
        <>
            <Head title="Welcome" />
            <div className="bg-background text-foreground flex min-h-screen flex-col items-center p-6 lg:justify-center lg:p-8">
                {flash?.error && (
                    <Alert
                        variant="destructive"
                        className="mb-4 w-full max-w-md"
                    >
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                {flash?.success && (
                    <Alert className="mb-4 w-full max-w-md border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 className="h-4 w-4" />
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                )}

                <Card className="w-full max-w-md shadow-lg">
                    <CardHeader className="text-center">
                        <div className="bg-primary mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full">
                            <FileText className="text-primary-foreground h-7 w-7" />
                        </div>
                        <CardTitle className="text-2xl">
                            Оставить заявку
                        </CardTitle>
                        <CardDescription>
                            Заполните форму, и мы свяжемся с вами в ближайшее
                            время
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form
                            onSubmit={handleSubmit(onSubmit)}
                            className="flex flex-col gap-4"
                            noValidate
                        >
                            <FormField
                                label="Имя"
                                error={errors.name?.message}
                                icon={<User className="h-4 w-4" />}
                                input={
                                    <Input
                                        placeholder="Иван Иванов"
                                        {...register('name')}
                                    />
                                }
                            />

                            <FormField
                                label="Email"
                                error={errors.email?.message}
                                icon={<Mail className="h-4 w-4" />}
                                input={
                                    <Input
                                        type="email"
                                        placeholder="example@mail.com"
                                        {...register('email')}
                                    />
                                }
                            />

                            <FormField
                                label="Телефон"
                                error={errors.phone?.message}
                                icon={<Phone className="h-4 w-4" />}
                                input={
                                    <Input
                                        type="tel"
                                        placeholder="+7 (999) 123-45-67"
                                        {...register('phone', {
                                            onChange: (event) => {
                                                event.target.value =
                                                    formatPhoneInput(
                                                        event.target.value,
                                                    );
                                            },
                                        })}
                                    />
                                }
                            />

                            <FormField
                                label="Цена"
                                error={errors.price?.message}
                                icon={<DollarSign className="h-4 w-4" />}
                                input={
                                    <Input
                                        type="text"
                                        inputMode="decimal"
                                        placeholder="1000.00"
                                        {...register('price')}
                                    />
                                }
                            />

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Отправка…
                                    </>
                                ) : (
                                    <>
                                        Отправить заявку
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </>
                                )}
                            </Button>

                            {errors.root && (
                                <Alert variant="destructive">
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>
                                        {errors.root.message as string}
                                    </AlertDescription>
                                </Alert>
                            )}
                        </form>
                    </CardContent>
                </Card>

                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}

function applyServerErrors(
    errors: Record<string, string | string[]>,
    setError: UseFormSetError<FormValues>,
) {
    Object.entries(errors).forEach(([field, message]) => {
        const errorMessage = Array.isArray(message)
            ? message[0]
            : String(message);

        if (['name', 'email', 'phone', 'price'].includes(field)) {
            setError(field as keyof FormValues, {
                type: 'server',
                message: errorMessage,
            });
        } else {
            setError('root' as any, { type: 'server', message: errorMessage });
        }
    });
}

interface FormFieldProps {
    label: string;
    error?: string;
    input: React.ReactElement<{ className?: string }>;
    icon?: React.ReactNode;
}

function FormField({ label, error, input, icon }: FormFieldProps) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            <div className="relative">
                {icon && (
                    <div className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 -translate-y-1/2">
                        {icon}
                    </div>
                )}
                {icon
                    ? React.cloneElement(input, { className: 'pl-10' })
                    : input}
            </div>

            {error && (
                <p className="text-destructive animate-in fade-in slide-in-from-top-1 flex items-center gap-1 text-xs duration-200">
                    <AlertCircle className="h-3 w-3 flex-shrink-0 animate-pulse" />
                    {error}
                </p>
            )}
        </div>
    );
}
