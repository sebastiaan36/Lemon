export type HomeService = {
    name: string;
    description: string;
    tags: string[];
    mediaUrl: string | null;
    mediaType?: 'image' | 'video';
};

export type HomeCase = {
    name: string;
    slug: string;
    video: string | null;
    photo: string | null;
};
