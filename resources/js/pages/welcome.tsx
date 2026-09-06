import { Head, Link, usePage, router } from '@inertiajs/react';
import { dashboard, login } from '@/routes';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import React, { useEffect, useRef, useState } from 'react';

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

import { User, Mail, Phone, DollarSign, FileText, Loader2, ArrowRight, AlertCircle, CheckCircle2 } from 'lucide-react';

const schema = z.object({
    name: z.string().min(2, 'Минимум 2 символа'),
    email: z.string().min(1, 'Введите email').email('Некорректный email'),
    phone: z.string().min(5, 'Введите телефон'),
    price: z.string()
        .min(1, 'Введите цену')
        .refine((val) => !Number.isNaN(Number(val)) && Number(val) > 0, {
            message: 'Цена должна быть положительным числом',
        }),
});

type FormValues = z.infer<typeof schema>;

export default function Welcome() {
    const { auth, errors: pageErrors, flash } = usePage().props as {
        auth: { user?: unknown };
        errors?: Record<string, string | string[]>
        flash?: { error?: string | null; success?: string | null };
    };
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

    const [success, setSuccess] = useState(false);

    useEffect(() => {
        if (!pageErrors) return;
        Object.entries(pageErrors).forEach(([field, message]) => {
            const errorMessage = Array.isArray(message) ? message[0] : String(message);

            if (['name', 'email', 'phone', 'price'].includes(field)) {
                setError(field as keyof FormValues, { type: 'server', message: errorMessage });
            } else {
                setError('root' as any, { type: 'server', message: errorMessage });
            }
        });
    }, [pageErrors, setError]);

    const onSubmit = (data: FormValues) => {
        setSuccess(false);
        const spentMoreThan30s = Date.now() - startedAt.current >= 30_000;

        router.post('/lead', { ...data, spent_more_than_30_seconds: spentMoreThan30s }, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setSuccess(true);
                setTimeout(() => setSuccess(false), 5000);
            },
        });
    };

    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col items-center bg-background p-6 text-foreground lg:justify-center lg:p-8">

                {flash?.error && (
                    <Alert variant="destructive" className="mb-4 w-full max-w-md">
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
                        <div className="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-primary">
                            <FileText className="h-7 w-7 text-primary-foreground" />
                        </div>
                        <CardTitle className="text-2xl">Оставить заявку</CardTitle>
                        <CardDescription>
                            Заполните форму, и мы свяжемся с вами в ближайшее время
                        </CardDescription>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4" noValidate>
                            <FormField
                                label="Имя"
                                error={errors.name?.message}
                                icon={<User className="h-4 w-4" />}
                                input={<Input placeholder="Иван Иванов" {...register('name')} />}
                            />

                            <FormField
                                label="Email"
                                error={errors.email?.message}
                                icon={<Mail className="h-4 w-4" />}
                                input={<Input type="email" placeholder="example@mail.com" {...register('email')} />}
                            />

                            <FormField
                                label="Телефон"
                                error={errors.phone?.message}
                                icon={<Phone className="h-4 w-4" />}
                                input={<Input type="tel" placeholder="+7 (999) 123-45-67" {...register('phone')} />}
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

                            <Button type="submit" className="mt-2 w-full" disabled={isSubmitting}>
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
                                    <AlertDescription>{errors.root.message as string}</AlertDescription>
                                </Alert>
                            )}

                            {success && (
                                <Alert className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <CheckCircle2 className="h-4 w-4" />
                                    <AlertDescription>Заявка успешно отправлена!</AlertDescription>
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
                    <div className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                        {icon}
                    </div>
                )}
                {icon ? React.cloneElement(input, { className: 'pl-10' }) : input}
            </div>

            {error && (
                <p className="flex items-center gap-1 text-xs text-destructive animate-in fade-in slide-in-from-top-1 duration-200">
                    <AlertCircle className="h-3 w-3 flex-shrink-0 animate-pulse" />
                    {error}
                </p>
            )}
        </div>
    );
}
